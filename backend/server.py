from fastapi import (
    FastAPI,
    APIRouter,
    HTTPException,
    UploadFile,
    File,
    Form,
    Depends,
    Query,
)
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv
from motor.motor_asyncio import AsyncIOMotorClient
import os
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker
from sqlalchemy import text, DateTime, Column, func, bindparam
import logging
from pathlib import Path
from pydantic import BaseModel, Field, ConfigDict
from typing import List, Optional, Dict, Any, Union
from uuid import uuid4
from datetime import datetime, date, timezone, timedelta
from enum import Enum
import bcrypt
from decimal import Decimal
from contextlib import asynccontextmanager
import uuid

# Load environment variables
ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / ".env")

DATABASE_URL = os.getenv("DATABASE_URL").replace(
    "postgresql://", "postgresql+asyncpg://"
)

DATABASE_URL = os.getenv("DATABASE_URL").replace(
    "postgresql://", "postgresql+asyncpg://"
)

# Disable prepared statement caching to avoid InvalidCachedStatementError
engine = create_async_engine(
    DATABASE_URL, echo=True, execution_options={"prepared_statement_cache_size": 0}
)

async_session = sessionmaker(bind=engine, class_=AsyncSession, expire_on_commit=False)
db = sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)
# Create the main app
app = FastAPI(title="Insurance Policy Management System", version="1.0.0")
api_router = APIRouter(prefix="/api")


# Enums
class UserRole(str, Enum):
    ADMIN = "ADMIN"
    AGENT = "AGENT"
    CLIENT = "CLIENT"


class EntityType(str, Enum):
    EMPLOYEE = "EMPLOYEE"
    STUDENT = "STUDENT"
    VEHICLE = "VEHICLE"
    BUILDING = "BUILDING"
    SHIP = "SHIP"


class InsuranceType(str, Enum):
    HEALTH = "HEALTH"
    ACCIDENT = "ACCIDENT"
    PROPERTY = "PROPERTY"
    VEHICLE = "VEHICLE"
    MARINE = "MARINE"


class PolicyStatus(str, Enum):
    ACTIVE = "ACTIVE"
    EXPIRED = "EXPIRED"
    UNDER_REVIEW = "UNDER_REVIEW"
    CANCELLED = "CANCELLED"


class EntityStatus(str, Enum):
    ACTIVE = "ACTIVE"
    INACTIVE = "INACTIVE"


class DocumentType(str, Enum):
    POLICY_DOCUMENT = "POLICY_DOCUMENT"
    ENDORSEMENT_DOCUMENT = "ENDORSEMENT_DOCUMENT"
    FINANCIAL_DOCUMENT = "FINANCIAL_DOCUMENT"
    OTHER = "OTHER"


class EndorsementChangeType(str, Enum):
    ADDED = "ADDED"
    REMOVED = "REMOVED"
    MODIFIED = "MODIFIED"
    STATUS_CHANGE = "STATUS_CHANGE"


class InvoiceStatus(str, Enum):
    DRAFT = "DRAFT"
    ISSUED = "ISSUED"
    CANCELLED = "CANCELLED"
    PAID = "PAID"


# Helper functions
def prepare_for_mongo(data: dict) -> dict:
    """Prepare data for MongoDB storage by converting non-serializable types"""
    for key, value in data.items():
        if isinstance(value, date) and not isinstance(value, datetime):
            data[key] = value.isoformat()
        elif isinstance(value, datetime):
            data[key] = value.isoformat()
        elif isinstance(value, Decimal):
            data[key] = float(value)
    return data


def parse_from_mongo(item: dict) -> dict:
    """Parse data from MongoDB by converting string dates back to date objects"""
    date_fields = [
        "start_date",
        "end_date",
        "effective_date",
        "audit_date",
        "invoice_date",
        "credit_date",
        "statement_date",
    ]
    datetime_fields = [
        "created_at",
        "updated_at",
        "uploaded_at",
        "action_timestamp",
        "changed_at",
    ]

    for field in date_fields:
        if field in item and isinstance(item[field], str):
            try:
                item[field] = datetime.fromisoformat(item[field]).date()
            except:
                pass

    for field in datetime_fields:
        if field in item and isinstance(item[field], str):
            try:
                item[field] = datetime.fromisoformat(item[field])
            except:
                pass

    return item


# Pydantic Models
class CompanyBase(BaseModel):
    name: str
    parent_company_id: Optional[str] = None


class Company(CompanyBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class UserBase(BaseModel):
    company_id: str
    name: str
    email: str
    role: UserRole


class UserCreate(UserBase):
    password: str


class User(UserBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    password_hash: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class EmployeeBase(BaseModel):
    company_id: str
    employee_code: str
    name: str
    status: EntityStatus = EntityStatus.ACTIVE
    department: Optional[str] = None
    position: Optional[str] = None


class Employee(EmployeeBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Column(DateTime(timezone=True), server_default=func.now())
    updated_at: datetime = Column(DateTime(timezone=True), onupdate=func.now())


class StudentBase(BaseModel):
    company_id: str
    student_id: str
    name: str
    status: EntityStatus = EntityStatus.ACTIVE
    course: Optional[str] = None
    year_of_study: Optional[int] = None


class Student(StudentBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Column(DateTime(timezone=True), server_default=func.now())
    updated_at: datetime = Column(DateTime(timezone=True), onupdate=func.now())


class VesselBase(BaseModel):
    company_id: str
    vessel_name: str
    imo_number: str
    status: EntityStatus = EntityStatus.ACTIVE
    vessel_type: Optional[str] = None
    flag: Optional[str] = None


class Vessel(VesselBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Column(DateTime(timezone=True), server_default=func.now())
    updated_at: datetime = Column(DateTime(timezone=True), onupdate=func.now())


class VehicleBase(BaseModel):
    company_id: str
    registration_number: str
    make: str
    model: str
    year: int
    status: EntityStatus = EntityStatus.ACTIVE


class Vehicle(VehicleBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Column(DateTime(timezone=True), server_default=func.now())
    updated_at: datetime = Column(DateTime(timezone=True), onupdate=func.now())


class EntityBase(BaseModel):
    company_id: str
    type: EntityType
    entity_id: str  # Reference to specific entity (employee, student, etc.)
    description: Optional[str] = None


class Entity(EntityBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class InsurancePolicyBase(BaseModel):
    entity_id: str
    policy_number: str
    insurance_type: InsuranceType
    provider: str
    start_date: date
    end_date: date
    sum_insured: float
    premium_amount: float
    status: PolicyStatus = PolicyStatus.ACTIVE
    created_by: str


class InsurancePolicy(InsurancePolicyBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class PolicyEndorsementBase(BaseModel):
    policy_id: str
    endorsement_number: str
    description: str
    effective_date: date
    created_by: str


class PolicyEndorsement(PolicyEndorsementBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class DocumentBase(BaseModel):
    policy_id: Optional[str] = None
    endorsement_id: Optional[str] = None
    uploaded_by: str
    file_name: str
    file_path: str
    file_type: str
    document_type: DocumentType


class Document(DocumentBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    uploaded_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


# Dashboard stats model
class DashboardStats(BaseModel):
    total_policies: int = 0
    active_policies: int = 0
    expired_policies: int = 0
    total_premium: float = 0.0
    total_entities: int = 0
    recent_endorsements: int = 0


# API Routes


# Authentication routes
# Authentication routes
@api_router.post("/auth/register", response_model=User)
async def register(user_data: UserCreate):
    async with db() as session:
        # Check if user already exists
        result = await session.execute(
            text("SELECT 1 FROM users WHERE email = :email"), {"email": user_data.email}
        )
        if result.first():
            raise HTTPException(status_code=400, detail="User already exists")

        # Hash password
        password_hash = bcrypt.hashpw(
            user_data.password.encode("utf-8"), bcrypt.gensalt()
        ).decode("utf-8")

        # Insert user
        user_obj = User(**user_data.model_dump(), password_hash=password_hash)
        insert_query = """
        INSERT INTO users (id, company_id, name, email, role, password_hash, created_at, updated_at)
        VALUES (:id, :company_id, :name, :email, :role, :password_hash, :created_at, :updated_at)
        """
        await session.execute(text(insert_query), user_obj.model_dump())
        await session.commit()
        return user_obj


@api_router.post("/auth/login")
async def login(email: str = Form(...), password: str = Form(...)):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM users WHERE email = :email"), {"email": email}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=401, detail="Invalid credentials")
        user = dict(row._mapping)
        password_matches = bcrypt.checkpw(
            password.encode("utf-8"), user["password_hash"].encode("utf-8")
        )
        if not password_matches:
            raise HTTPException(status_code=401, detail="Invalid credentials")
        return {"message": "Login successful", "user": user}


# Company routes
@api_router.post("/companies", response_model=Company)
async def create_company(company_data: CompanyBase):
    async with db() as session:
        company_obj = Company(**company_data.model_dump())
        insert_query = """
        INSERT INTO companies (id, name, parent_company_id, created_at, updated_at)
        VALUES (:id, :name, :parent_company_id, :created_at, :updated_at)
        """
        await session.execute(text(insert_query), company_obj.model_dump())
        await session.commit()
        return company_obj


@api_router.get("/companies", response_model=List[Company])
async def get_companies():
    async with db() as session:
        result = await session.execute(text("SELECT * FROM companies"))
        companies = [dict(row._mapping) for row in result.fetchall()]
        return companies


@api_router.get("/companies/{company_id}", response_model=Company)
async def get_company(company_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM companies WHERE id = :id"), {"id": company_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Company not found")
        return dict(row._mapping)


@api_router.put("/companies/{company_id}", response_model=Company)
async def update_company(company_id: str, company_data: CompanyBase):
    async with db() as session:
        update_query = """
        UPDATE companies
        SET name = :name,
            parent_company_id = :parent_company_id,
            updated_at = :updated_at
        WHERE id = :id
        RETURNING *
        """
        result = await session.execute(
            text(update_query),
            {
                "id": company_id,
                "name": company_data.name,
                "parent_company_id": company_data.parent_company_id,
                "updated_at": datetime.now(timezone.utc),
            },
        )
        row = result.first()
        await session.commit()
        if not row:
            raise HTTPException(status_code=404, detail="Company not found")
        return dict(row._mapping)


@api_router.delete("/companies/{company_id}")
async def delete_company(company_id: str):
    async with db() as session:
        result = await session.execute(
            text("DELETE FROM companies WHERE id = :id"), {"id": company_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Company not found")
        return {"message": "Company deleted"}


# Employee routes
@api_router.post("/employees", response_model=Employee)
async def create_employee(employee_data: EmployeeBase):
    async with db() as session:
        # ✅ Check for duplicate employee code (UUID-safe)
        result = await session.execute(
            text("""
                SELECT 1 FROM employees
                WHERE employee_code = :employee_code
                AND company_id = CAST(:company_id AS uuid)
            """),
            {
                "employee_code": employee_data.employee_code,
                "company_id": str(employee_data.company_id),
            },
        )

        if result.first():
            raise HTTPException(status_code=400, detail="Employee code already exists")

        now = datetime.now(timezone.utc)

        # ✅ Prepare Employee object
        employee_obj = {
            "id": str(uuid4()),
            "company_id": str(employee_data.company_id),
            "employee_code": employee_data.employee_code,
            "name": employee_data.name,
            "status": (
                employee_data.status.value
                if isinstance(employee_data.status, Enum)
                else employee_data.status
            ),
            "department": employee_data.department,
            "position": employee_data.position,
            "created_at": now,
            "updated_at": now,
        }

        # ✅ Prepare related Entity object
        entity_obj = {
            "id": str(uuid4()),
            "company_id": str(employee_data.company_id),
            "type": "EMPLOYEE",
            "entity_id": employee_obj["id"],
            "description": f"Employee: {employee_data.name}",
            "created_at": now,
            "updated_at": now,
        }

        # ✅ Employee insert query (cast UUIDs safely)
        insert_employee = text("""
            INSERT INTO employees (
                id, company_id, employee_code, name, status, department, position, created_at, updated_at
            ) VALUES (
                CAST(:id AS uuid),
                CAST(:company_id AS uuid),
                :employee_code,
                :name,
                :status,
                :department,
                :position,
                :created_at,
                :updated_at
            )
        """).bindparams(
            bindparam("created_at", type_=DateTime(timezone=True)),
            bindparam("updated_at", type_=DateTime(timezone=True)),
        )

        # ✅ Entity insert query (cast UUIDs safely)
        insert_entity = text("""
            INSERT INTO entities (
                id, company_id, type, entity_id, description, created_at, updated_at
            ) VALUES (
                CAST(:id AS uuid),
                CAST(:company_id AS uuid),
                :type,
                CAST(:entity_id AS uuid),
                :description,
                :created_at,
                :updated_at
            )
        """).bindparams(
            bindparam("created_at", type_=DateTime(timezone=True)),
            bindparam("updated_at", type_=DateTime(timezone=True)),
        )

        # ✅ Execute safely with rollback on error
        try:
            await session.execute(insert_employee, employee_obj)
            await session.execute(insert_entity, entity_obj)
            await session.commit()
            return employee_obj
        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(
                status_code=500,
                detail=f"Failed to create employee or entity: {str(e)}"
            )


@api_router.get("/employees", response_model=List[Employee])
async def get_employees(company_id: Optional[str] = Query(None)):
    async with db() as session:
        # ✅ Build query dynamically based on filter
        if company_id:
            query = text("""
                SELECT * FROM employees 
                WHERE company_id = CAST(:company_id AS uuid)
                ORDER BY created_at DESC
            """)
            result = await session.execute(query, {"company_id": str(company_id)})
        else:
            result = await session.execute(
                text("SELECT * FROM employees ORDER BY created_at DESC")
            )

        rows = result.fetchall()
        employees = []

        for row in rows:
            data = dict(row._mapping)

            # ✅ Convert UUIDs to strings for JSON serialization
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)

                elif isinstance(value, datetime):
                    # Convert datetime to ISO format with timezone
                    data[key] = value.isoformat()

                elif isinstance(value, str) and key in ["created_at", "updated_at"]:
                    # Handle PostgreSQL timestamp strings (e.g., "2025-11-04 07:40:51.405604+00")
                    val = value.replace(" ", "T").replace("+00", "+00:00")
                    data[key] = val

            employees.append(data)

        if not employees:
            raise HTTPException(status_code=404, detail="No employees found")

        return employees


@api_router.get("/employees/{employee_id}", response_model=Employee)
async def get_employee(employee_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM employees WHERE id = :id"), {"id": employee_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Employee not found")
        return dict(row._mapping)


@api_router.put("/employees/{employee_id}", response_model=Employee)
async def update_employee(employee_id: str, employee_data: EmployeeBase):
    async with db() as session:
        now = datetime.now(timezone.utc)

        try:
            # ✅ Update employee record
            update_employee_query = text("""
                UPDATE employees SET
                    company_id = CAST(:company_id AS uuid),
                    employee_code = :employee_code,
                    name = :name,
                    status = :status,
                    department = :department,
                    position = :position,
                    updated_at = :updated_at
                WHERE id = CAST(:id AS uuid)
                RETURNING *
            """)

            result = await session.execute(
                update_employee_query,
                {
                    "id": employee_id,
                    "company_id": str(employee_data.company_id),
                    "employee_code": employee_data.employee_code,
                    "name": employee_data.name,
                    "status": (
                        employee_data.status.value
                        if isinstance(employee_data.status, Enum)
                        else employee_data.status
                    ),
                    "department": employee_data.department,
                    "position": employee_data.position,
                    "updated_at": now,
                },
            )

            row = result.first()
            if not row:
                await session.rollback()
                raise HTTPException(status_code=404, detail="Employee not found")

            # ✅ Fix: cast param to uuid properly
            update_entity_query = text("""
                UPDATE entities
                SET description = :desc,
                    updated_at = :updated_at
                WHERE entity_id = CAST(:eid AS uuid)
                AND type = :type
            """)

            await session.execute(
                update_entity_query,
                {
                    "desc": f"Employee: {employee_data.name}",
                    "updated_at": now,
                    "eid": employee_id,
                    "type": "EMPLOYEE",
                },
            )

            await session.commit()

            # ✅ Normalize response data
            data = dict(row._mapping)
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()

            return data

        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(status_code=500, detail=f"Failed to update employee: {e}")


@api_router.delete("/employees/{employee_id}")
async def delete_employee(employee_id: str):
    async with db() as session:
        # Delete related entity record first
        await session.execute(
            text("DELETE FROM entities WHERE entity_id = :eid AND type = :type"),
            {"eid": employee_id, "type": EntityType.EMPLOYEE.value},
        )
        result = await session.execute(
            text("DELETE FROM employees WHERE id = :id"), {"id": employee_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Employee not found")
        return {"message": "Employee deleted"}


# Student routes
@api_router.post("/students", response_model=Student)
async def create_student(student_data: StudentBase):
    async with db() as session:
        result = await session.execute(
            text(
                "SELECT 1 FROM students WHERE student_id = :student_id AND company_id = :company_id"
            ),
            {
                "student_id": student_data.student_id,
                "company_id": student_data.company_id,
            },
        )
        if result.first():
            raise HTTPException(status_code=400, detail="Student ID already exists")

        student_obj = Student(**student_data.model_dump())
        insert_query = """
        INSERT INTO students (id, company_id, student_id, name, status, course, year_of_study, created_at, updated_at)
        VALUES (:id, :company_id, :student_id, :name, :status, :course, :year_of_study, :created_at, :updated_at)
        """
        await session.execute(text(insert_query), student_obj.model_dump())

        # Create entity
        entity_obj = Entity(
            company_id=student_data.company_id,
            type=EntityType.STUDENT,
            entity_id=student_obj.id,
            description=f"Student: {student_data.name}",
        )
        insert_entity = """
        INSERT INTO entities (id, company_id, type, entity_id, description, created_at, updated_at)
        VALUES (:id, :company_id, :type, :entity_id, :description, :created_at, :updated_at)
        """
        await session.execute(text(insert_entity), entity_obj.model_dump())
        await session.commit()
        return student_obj


@api_router.get("/students", response_model=List[Student])
async def get_students(company_id: Optional[str] = Query(None)):
    async with db() as session:
        # ✅ Fetch students based on company_id (if provided)
        if company_id:
            query = text("""
                SELECT * FROM students 
                WHERE company_id = CAST(:company_id AS uuid)
                ORDER BY created_at DESC
            """)
            result = await session.execute(query, {"company_id": str(company_id)})
        else:
            result = await session.execute(text("SELECT * FROM students ORDER BY created_at DESC"))

        rows = result.fetchall()
        students = []

        for row in rows:
            data = dict(row._mapping)

            # ✅ Normalize UUIDs and timestamps
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()
                elif isinstance(value, str) and key in ["created_at", "updated_at"]:
                    # Convert PostgreSQL string timestamps to valid ISO 8601 format
                    if " " in value:
                        value = value.replace(" ", "T")
                    if value.endswith("+00"):
                        value = value.replace("+00", "+00:00")
                    data[key] = value

            students.append(data)

        return students



@api_router.get("/students/{student_id}", response_model=Student)
async def get_student(student_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM students WHERE id = :id"), {"id": student_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Student not found")
        return dict(row._mapping)


@api_router.put("/students/{student_id}", response_model=Student)
async def update_student(student_id: str, student_data: StudentBase):
    async with db() as session:
        now = datetime.now(timezone.utc)

        try:
            # ✅ Update student record
            update_query = text("""
                UPDATE students SET
                    company_id = CAST(:company_id AS uuid),
                    student_id = :student_id,
                    name = :name,
                    status = :status,
                    course = :course,
                    year_of_study = :year_of_study,
                    updated_at = :updated_at
                WHERE id = CAST(:id AS uuid)
                RETURNING *
            """)

            result = await session.execute(
                update_query,
                {
                    "id": student_id,
                    "company_id": str(student_data.company_id),
                    "student_id": student_data.student_id,
                    "name": student_data.name,
                    "status": (
                        student_data.status.value
                        if isinstance(student_data.status, Enum)
                        else student_data.status
                    ),
                    "course": student_data.course,
                    "year_of_study": student_data.year_of_study,
                    "updated_at": now,
                },
            )

            row = result.first()
            if not row:
                await session.rollback()
                raise HTTPException(status_code=404, detail="Student not found")

            # ✅ Update related entity description
            update_entity_query = text("""
                UPDATE entities
                SET description = :desc,
                    updated_at = :updated_at
                WHERE entity_id = CAST(:eid AS uuid)
                AND type = :type
            """)

            await session.execute(
                update_entity_query,
                {
                    "desc": f"Student: {student_data.name}",
                    "updated_at": now,
                    "eid": student_id,
                    "type": EntityType.STUDENT.value,
                },
            )

            await session.commit()

            # ✅ Normalize response data
            data = dict(row._mapping)
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()

            return data

        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(status_code=500, detail=f"Failed to update student: {e}")


@api_router.delete("/students/{student_id}")
async def delete_student(student_id: str):
    async with db() as session:
        await session.execute(
            text("DELETE FROM entities WHERE entity_id = :eid AND type = :type"),
            {"eid": student_id, "type": EntityType.STUDENT.value},
        )
        result = await session.execute(
            text("DELETE FROM students WHERE id = :id"), {"id": student_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Student not found")
        return {"message": "Student deleted"}


@api_router.post("/vessels", response_model=Vessel)
async def create_vessel(vessel_data: VesselBase):
    async with db() as session:
        # 🟢 Check for duplicate IMO number
        result = await session.execute(
            text("SELECT 1 FROM vessels WHERE imo_number = :imo_number"),
            {"imo_number": vessel_data.imo_number},
        )
        if result.first():
            raise HTTPException(status_code=400, detail="IMO number already exists")

        now = datetime.now(timezone.utc)

        vessel_obj = {
            "id": str(uuid4()),
            "company_id": vessel_data.company_id,
            "vessel_name": vessel_data.vessel_name,
            "imo_number": vessel_data.imo_number,
            "status": (
                vessel_data.status.value
                if isinstance(vessel_data.status, Enum)
                else vessel_data.status
            ),
            "vessel_type": vessel_data.vessel_type,
            "flag": vessel_data.flag,
            "created_at": now,
            "updated_at": now,
        }

        entity_obj = {
            "id": str(uuid4()),
            "company_id": vessel_data.company_id,
            "type": "SHIP",
            "entity_id": vessel_obj["id"],
            "description": f"Vessel: {vessel_data.vessel_name}",
            "created_at": now,
            "updated_at": now,
        }

        # ✅ Define insert statements
        insert_vessel = text(
            """
            INSERT INTO vessels (
                id, company_id, vessel_name, imo_number, status, vessel_type, flag, created_at, updated_at
            ) VALUES (
                :id, :company_id, :vessel_name, :imo_number, :status, :vessel_type, :flag, :created_at, :updated_at
            )
        """
        ).bindparams(
            bindparam("created_at", type_=DateTime(timezone=True)),
            bindparam("updated_at", type_=DateTime(timezone=True)),
        )

        insert_entity = text(
            """
            INSERT INTO entities (
                id, company_id, type, entity_id, description, created_at, updated_at
            ) VALUES (
                :id, :company_id, :type, :entity_id, :description, :created_at, :updated_at
            )
        """
        ).bindparams(
            bindparam("created_at", type_=DateTime(timezone=True)),
            bindparam("updated_at", type_=DateTime(timezone=True)),
        )

        try:
            # 🟢 Execute both inserts
            await session.execute(insert_vessel, vessel_obj)
            await session.execute(insert_entity, entity_obj)
            await session.commit()

            return vessel_obj

        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(
                status_code=500, detail=f"Failed to create vessel or entity: {e}"
            )


@api_router.get("/vessels", response_model=List[Vessel])
async def get_vessels(company_id: Optional[str] = Query(None)):
    async with db() as session:
        query = "SELECT * FROM vessels"
        params = {}

        if company_id:
            query += " WHERE company_id = :company_id"
            params["company_id"] = company_id

        result = await session.execute(text(query), params)
        rows = result.fetchall()

        vessels = []
        for row in rows:
            data = dict(row._mapping)
            # 🧠 Convert UUIDs to strings so Pydantic doesn’t break
            if isinstance(data.get("id"), uuid.UUID):
                data["id"] = str(data["id"])
            if isinstance(data.get("company_id"), uuid.UUID):
                data["company_id"] = str(data["company_id"])
            vessels.append(data)

        return vessels


@api_router.get("/vessels/{vessel_id}", response_model=Vessel)
async def get_vessel(vessel_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM vessels WHERE id = :id"), {"id": vessel_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Vessel not found")
        return dict(row._mapping)


@api_router.put("/vessels/{vessel_id}", response_model=Vessel)
async def update_vessel(vessel_id: str, vessel_data: VesselBase):
    async with db() as session:
        now = datetime.now(timezone.utc)

        try:
            # ✅ Update vessel record
            update_vessel_query = text("""
                UPDATE vessels SET
                    company_id = CAST(:company_id AS uuid),
                    vessel_name = :vessel_name,
                    imo_number = :imo_number,
                    status = :status,
                    vessel_type = :vessel_type,
                    flag = :flag,
                    updated_at = :updated_at
                WHERE id = CAST(:id AS uuid)
                RETURNING *
            """)

            result = await session.execute(
                update_vessel_query,
                {
                    "id": vessel_id,
                    "company_id": str(vessel_data.company_id),
                    "vessel_name": vessel_data.vessel_name,
                    "imo_number": vessel_data.imo_number,
                    "status": (
                        vessel_data.status.value
                        if isinstance(vessel_data.status, Enum)
                        else vessel_data.status
                    ),
                    "vessel_type": vessel_data.vessel_type,
                    "flag": vessel_data.flag,
                    "updated_at": now,
                },
            )

            row = result.first()
            if not row:
                await session.rollback()
                raise HTTPException(status_code=404, detail="Vessel not found")

            # ✅ Update related entity description
            update_entity_query = text("""
                UPDATE entities
                SET description = :desc,
                    updated_at = :updated_at
                WHERE entity_id = CAST(:eid AS uuid)
                AND type = :type
            """)

            await session.execute(
                update_entity_query,
                {
                    "desc": f"Vessel: {vessel_data.vessel_name}",
                    "updated_at": now,
                    "eid": vessel_id,
                    "type": EntityType.SHIP.value,
                },
            )

            await session.commit()

            # ✅ Normalize response data
            data = dict(row._mapping)
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()

            return data

        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(status_code=500, detail=f"Failed to update vessel: {e}")



@api_router.delete("/vessels/{vessel_id}")
async def delete_vessel(vessel_id: str):
    async with db() as session:
        await session.execute(
            text("DELETE FROM entities WHERE entity_id = :eid AND type = :type"),
            {"eid": vessel_id, "type": EntityType.SHIP.value},
        )
        result = await session.execute(
            text("DELETE FROM vessels WHERE id = :id"), {"id": vessel_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Vessel not found")
        return {"message": "Vessel deleted"}


# Vehicle routes
@api_router.post("/vehicles", response_model=Vehicle)
async def create_vehicle(vehicle_data: VehicleBase):
    async with db() as session:
        result = await session.execute(
            text(
                "SELECT 1 FROM vehicles WHERE registration_number = :registration_number"
            ),
            {"registration_number": vehicle_data.registration_number},
        )
        if result.first():
            raise HTTPException(
                status_code=400, detail="Registration number already exists"
            )

        vehicle_obj = Vehicle(**vehicle_data.model_dump())
        insert_query = """
        INSERT INTO vehicles (id, company_id, registration_number, make, model, year, status, created_at, updated_at)
        VALUES (:id, :company_id, :registration_number, :make, :model, :year, :status, :created_at, :updated_at)
        """
        await session.execute(text(insert_query), vehicle_obj.model_dump())

        # Create entity
        entity_obj = Entity(
            company_id=vehicle_data.company_id,
            type=EntityType.VEHICLE,
            entity_id=vehicle_obj.id,
            description=f"Vehicle: {vehicle_data.make} {vehicle_data.model}",
        )
        insert_entity = """
        INSERT INTO entities (id, company_id, type, entity_id, description, created_at, updated_at)
        VALUES (:id, :company_id, :type, :entity_id, :description, :created_at, :updated_at)
        """
        await session.execute(text(insert_entity), entity_obj.model_dump())
        await session.commit()
        return vehicle_obj


@api_router.get("/vehicles", response_model=List[Vehicle])
async def get_vehicles(company_id: Optional[str] = Query(None)):
    async with db() as session:
        # ✅ Build query dynamically
        if company_id:
            query = text("""
                SELECT * FROM vehicles
                WHERE company_id = CAST(:company_id AS uuid)
                ORDER BY created_at DESC
            """)
            result = await session.execute(query, {"company_id": str(company_id)})
        else:
            result = await session.execute(text("SELECT * FROM vehicles ORDER BY created_at DESC"))

        rows = result.fetchall()
        vehicles = []

        # ✅ Normalize UUID and datetime fields
        for row in rows:
            data = dict(row._mapping)

            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()
                elif isinstance(value, str) and key in ["created_at", "updated_at"]:
                    # Fix non-ISO PostgreSQL timestamps
                    if " " in value:
                        value = value.replace(" ", "T")
                    if value.endswith("+00"):
                        value = value.replace("+00", "+00:00")
                    data[key] = value

            vehicles.append(data)

        return vehicles



@api_router.get("/vehicles/{vehicle_id}", response_model=Vehicle)
async def get_vehicle(vehicle_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM vehicles WHERE id = :id"), {"id": vehicle_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Vehicle not found")
        return dict(row._mapping)


@api_router.put("/vehicles/{vehicle_id}", response_model=Vehicle)
async def update_vehicle(vehicle_id: str, vehicle_data: VehicleBase):
    async with db() as session:
        now = datetime.now(timezone.utc)

        try:
            # ✅ Update vehicle record
            update_vehicle_query = text("""
                UPDATE vehicles SET
                    company_id = CAST(:company_id AS uuid),
                    registration_number = :registration_number,
                    make = :make,
                    model = :model,
                    year = :year,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = CAST(:id AS uuid)
                RETURNING *
            """)

            result = await session.execute(
                update_vehicle_query,
                {
                    "id": vehicle_id,
                    "company_id": str(vehicle_data.company_id),
                    "registration_number": vehicle_data.registration_number,
                    "make": vehicle_data.make,
                    "model": vehicle_data.model,
                    "year": vehicle_data.year,
                    "status": (
                        vehicle_data.status.value
                        if isinstance(vehicle_data.status, Enum)
                        else vehicle_data.status
                    ),
                    "updated_at": now,
                },
            )

            row = result.first()
            if not row:
                await session.rollback()
                raise HTTPException(status_code=404, detail="Vehicle not found")

            # ✅ Update related entity description
            update_entity_query = text("""
                UPDATE entities
                SET description = :desc,
                    updated_at = :updated_at
                WHERE entity_id = CAST(:eid AS uuid)
                AND type = :type
            """)

            await session.execute(
                update_entity_query,
                {
                    "desc": f"Vehicle: {vehicle_data.make} {vehicle_data.model}",
                    "updated_at": now,
                    "eid": vehicle_id,
                    "type": EntityType.VEHICLE.value,
                },
            )

            await session.commit()

            # ✅ Normalize response data
            data = dict(row._mapping)
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()

            return data

        except Exception as e:
            await session.rollback()
            print("DB Error:", e)
            raise HTTPException(status_code=500, detail=f"Failed to update vehicle: {e}")



@api_router.delete("/vehicles/{vehicle_id}")
async def delete_vehicle(vehicle_id: str):
    async with db() as session:
        await session.execute(
            text("DELETE FROM entities WHERE entity_id = :eid AND type = :type"),
            {"eid": vehicle_id, "type": EntityType.VEHICLE.value},
        )
        result = await session.execute(
            text("DELETE FROM vehicles WHERE id = :id"), {"id": vehicle_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Vehicle not found")
        return {"message": "Vehicle deleted"}


# Entity routes
@api_router.get("/entities", response_model=List[Entity])
async def get_entities(
    company_id: Optional[str] = Query(None),
    entity_type: Optional[EntityType] = Query(None),
):
    async with db() as session:
        # ✅ Base query
        query = "SELECT * FROM entities WHERE 1=1"
        params = {}

        # ✅ Optional filters
        if company_id:
            query += " AND company_id = CAST(:company_id AS uuid)"
            params["company_id"] = company_id
        if entity_type:
            query += " AND type = :type"
            params["type"] = (
                entity_type.value if isinstance(entity_type, Enum) else entity_type
            )

        # ✅ Execute query
        result = await session.execute(text(query), params)
        rows = result.fetchall()

        entities = []
        for row in rows:
            data = dict(row._mapping)
            for key, value in data.items():
                if isinstance(value, uuid.UUID):
                    data[key] = str(value)
                elif isinstance(value, datetime):
                    data[key] = value.isoformat()
            entities.append(data)

        return entities



# Policy routes
@api_router.post("/policies", response_model=InsurancePolicy)
async def create_policy(policy_data: InsurancePolicyBase):
    async with db() as session:
        # ✅ Check duplicate policy number (optional: per entity)
        result = await session.execute(
            text("""
                SELECT 1 FROM policies 
                WHERE policy_number = :policy_number AND entity_id = :entity_id
            """),
            {
                "policy_number": policy_data.policy_number,
                "entity_id": policy_data.entity_id,
            },
        )
        if result.first():
            raise HTTPException(status_code=400, detail="Policy number already exists")

        now = datetime.now(timezone.utc)

        # ✅ Build policy record with correct types
        policy_obj = {
            "id": str(uuid.uuid4()),
            "entity_id": str(policy_data.entity_id),
            "policy_number": policy_data.policy_number,
            "insurance_type": (
                policy_data.insurance_type.value
                if hasattr(policy_data.insurance_type, "value")
                else policy_data.insurance_type
            ),
            "provider": policy_data.provider,
            "start_date": policy_data.start_date.isoformat() if policy_data.start_date else None,
            "end_date": policy_data.end_date.isoformat() if policy_data.end_date else None,
            "sum_insured": float(policy_data.sum_insured),
            "premium_amount": float(policy_data.premium_amount),
            "status": (
                policy_data.status.value
                if hasattr(policy_data.status, "value")
                else policy_data.status
            ),
            "created_by": str(policy_data.created_by),
            "created_at": now,
            "updated_at": now,
        }

        insert_query = """
        INSERT INTO policies (
            id, entity_id, policy_number, insurance_type, provider,
            start_date, end_date, sum_insured, premium_amount,
            status, created_by, created_at, updated_at
        ) VALUES (
            :id, :entity_id, :policy_number, :insurance_type, :provider,
            :start_date, :end_date, :sum_insured, :premium_amount,
            :status, :created_by, :created_at, :updated_at
        )
        RETURNING *
        """

        result = await session.execute(text(insert_query), policy_obj)
        row = result.first()
        await session.commit()

        return dict(row._mapping)


@api_router.get("/policies", response_model=List[InsurancePolicy])
async def get_policies(
    company_id: Optional[str] = Query(None),
    entity_id: Optional[str] = Query(None),
    status: Optional[PolicyStatus] = Query(None),
    insurance_type: Optional[InsuranceType] = Query(None),
):
    async with db() as session:
        query = """
        SELECT * FROM policies
        WHERE 1=1
        """
        params = {}

        if company_id:
            query += " AND company_id = CAST(:company_id AS uuid)"
            params["company_id"] = company_id

        if entity_id:
            query += " AND entity_id = CAST(:entity_id AS uuid)"
            params["entity_id"] = entity_id

        if status:
            query += " AND status = :status"
            params["status"] = (
                status.value if isinstance(status, Enum) else status
            )

        if insurance_type:
            query += " AND insurance_type = :insurance_type"
            params["insurance_type"] = (
                insurance_type.value if isinstance(insurance_type, Enum) else insurance_type
            )

        # Execute query
        result = await session.execute(text(query), params)
        rows = result.fetchall()

        # Normalize UUIDs and datetimes
        policies = []
        for row in rows:
            record = dict(row._mapping)
            for key, value in record.items():
                if isinstance(value, uuid.UUID):
                    record[key] = str(value)
                elif isinstance(value, datetime):
                    record[key] = value.isoformat()
            policies.append(record)

        return policies



@api_router.get("/policies/{policy_id}", response_model=InsurancePolicy)
async def get_policy(policy_id: str):
    async with db() as session:
        result = await session.execute(
            text("SELECT * FROM policies WHERE id = :id"), {"id": policy_id}
        )
        row = result.first()
        if not row:
            raise HTTPException(status_code=404, detail="Policy not found")
        return dict(row._mapping)


@api_router.put("/policies/{policy_id}/status")
async def update_policy_status(policy_id: str, status: PolicyStatus):
    async with db() as session:
        result = await session.execute(
            text(
                "UPDATE policies SET status = :status, updated_at = :updated_at WHERE id = :id"
            ),
            {
                "status": status,
                "updated_at": datetime.now(timezone.utc),
                "id": policy_id,
            },
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Policy not found")
        return {"message": "Policy status updated"}


@api_router.put("/policies/{policy_id}", response_model=InsurancePolicy)
async def update_policy(policy_id: str, policy_data: InsurancePolicyBase):
    async with db() as session:
        # Ensure policy number uniqueness if changed
        current = await session.execute(
            text("SELECT policy_number FROM policies WHERE id = :id"), {"id": policy_id}
        )
        current_row = current.first()
        if not current_row:
            raise HTTPException(status_code=404, detail="Policy not found")
        current_number = current_row[0]
        if current_number != policy_data.policy_number:
            dupe = await session.execute(
                text("SELECT 1 FROM policies WHERE policy_number = :num"),
                {"num": policy_data.policy_number},
            )
            if dupe.first():
                raise HTTPException(
                    status_code=400, detail="Policy number already exists"
                )

        update_query = """
        UPDATE policies SET
            entity_id = :entity_id,
            policy_number = :policy_number,
            insurance_type = :insurance_type,
            provider = :provider,
            start_date = :start_date,
            end_date = :end_date,
            sum_insured = :sum_insured,
            premium_amount = :premium_amount,
            status = :status,
            created_by = :created_by,
            updated_at = :updated_at
        WHERE id = :id
        RETURNING *
        """
        result = await session.execute(
            text(update_query),
            {
                **policy_data.model_dump(),
                "id": policy_id,
                "updated_at": datetime.now(timezone.utc),
            },
        )
        row = result.first()
        await session.commit()
        if not row:
            raise HTTPException(status_code=404, detail="Policy not found")
        return dict(row._mapping)


@api_router.delete("/policies/{policy_id}")
async def delete_policy(policy_id: str):
    async with db() as session:
        # Delete endorsements and documents referencing the policy (if such tables exist)
        await session.execute(
            text("DELETE FROM endorsements WHERE policy_id = :pid"), {"pid": policy_id}
        )
        await session.execute(
            text("DELETE FROM documents WHERE policy_id = :pid"), {"pid": policy_id}
        )
        result = await session.execute(
            text("DELETE FROM policies WHERE id = :id"), {"id": policy_id}
        )
        await session.commit()
        if result.rowcount == 0:
            raise HTTPException(status_code=404, detail="Policy not found")
        return {"message": "Policy deleted"}


@api_router.get("/policies/expiring", response_model=List[InsurancePolicy])
async def get_expiring_policies(days: int = Query(30, ge=1, le=365)):
    async with db() as session:
        today = date.today()
        until = today + timedelta(days=days)
        query = text(
            """
            SELECT * FROM policies
            WHERE status = 'ACTIVE'
              AND end_date >= :today
              AND end_date <= :until
            ORDER BY end_date ASC
            """
        )
        result = await session.execute(
            query, {"today": today.isoformat(), "until": until.isoformat()}
        )
        rows = [dict(row._mapping) for row in result.fetchall()]
        return rows


# Endorsement routes
@api_router.post("/endorsements", response_model=PolicyEndorsement)
async def create_endorsement(endorsement_data: PolicyEndorsementBase):
    async with db() as session:
        result = await session.execute(
            text(
                "SELECT 1 FROM endorsements WHERE endorsement_number = :endorsement_number"
            ),
            {"endorsement_number": endorsement_data.endorsement_number},
        )
        if result.first():
            raise HTTPException(
                status_code=400, detail="Endorsement number already exists"
            )

        endorsement_obj = PolicyEndorsement(**endorsement_data.model_dump())
        insert_query = """
        INSERT INTO endorsements (id, policy_id, endorsement_number, description, effective_date, created_by, created_at, updated_at)
        VALUES (:id, :policy_id, :endorsement_number, :description, :effective_date, :created_by, :created_at, :updated_at)
        """
        await session.execute(text(insert_query), endorsement_obj.model_dump())
        await session.commit()
        return endorsement_obj


@api_router.get("/endorsements", response_model=List[PolicyEndorsement])
async def get_endorsements(policy_id: Optional[str] = Query(None)):
    async with db() as session:
        if policy_id:
            result = await session.execute(
                text("SELECT * FROM endorsements WHERE policy_id = :policy_id"),
                {"policy_id": policy_id},
            )
        else:
            result = await session.execute(text("SELECT * FROM endorsements"))
        endorsements = [dict(row._mapping) for row in result.fetchall()]
        return endorsements


# Dashboard stats
@api_router.get("/dashboard/stats", response_model=DashboardStats)
async def get_dashboard_stats():
    async with db() as session:
        result_total_policies = await session.execute(
            text("SELECT COUNT(*) FROM policies")
        )
        total_policies = result_total_policies.scalar()

        result_active = await session.execute(
            text("SELECT COUNT(*) FROM policies WHERE status = 'ACTIVE'")
        )
        active_policies = result_active.scalar()

        result_expired = await session.execute(
            text("SELECT COUNT(*) FROM policies WHERE status = 'EXPIRED'")
        )
        expired_policies = result_expired.scalar()

        result_premium = await session.execute(
            text("SELECT SUM(premium_amount) FROM policies WHERE status = 'ACTIVE'")
        )
        total_premium = result_premium.scalar() or 0.0

        result_entities = await session.execute(text("SELECT COUNT(*) FROM entities"))
        total_entities = result_entities.scalar()

        thirty_days_ago = datetime.now(timezone.utc) - timedelta(days=30)
        result_endorsements = await session.execute(
            text("SELECT COUNT(*) FROM endorsements WHERE created_at >= :date"),
            {"date": thirty_days_ago.isoformat()},  # ✅ pass as string
        )
        recent_endorsements = result_endorsements.scalar()

        return DashboardStats(
            total_policies=total_policies,
            active_policies=active_policies,
            expired_policies=expired_policies,
            total_premium=total_premium,
            total_entities=total_entities,
            recent_endorsements=recent_endorsements,
        )


# Search endpoint
@api_router.get("/search")
async def search(q: str = Query(...), entity_type: Optional[str] = Query(None)):
    async with db() as session:
        results = {"policies": [], "entities": [], "endorsements": []}

        # Policies
        policy_query = "SELECT * FROM policies WHERE policy_number ILIKE :q OR provider ILIKE :q LIMIT 10"
        policy_rows = await session.execute(text(policy_query), {"q": f"%{q}%"})
        results["policies"] = [dict(row._mapping) for row in policy_rows.fetchall()]

        # Entities
        if entity_type:
            etype = entity_type.upper()
            if etype == "EMPLOYEE":
                query = "SELECT * FROM employees WHERE name ILIKE :q OR employee_code ILIKE :q LIMIT 10"
            elif etype == "STUDENT":
                query = "SELECT * FROM students WHERE name ILIKE :q OR student_id ILIKE :q LIMIT 10"
            elif etype == "VESSEL":
                query = "SELECT * FROM vessels WHERE vessel_name ILIKE :q OR imo_number ILIKE :q LIMIT 10"
            elif etype == "VEHICLE":
                query = "SELECT * FROM vehicles WHERE registration_number ILIKE :q OR make ILIKE :q OR model ILIKE :q LIMIT 10"
            else:
                query = None
            if query:
                entity_rows = await session.execute(text(query), {"q": f"%{q}%"})
                results["entities"] = [
                    dict(row._mapping) for row in entity_rows.fetchall()
                ]

        # Endorsements
        endorsement_query = "SELECT * FROM endorsements WHERE endorsement_number ILIKE :q OR description ILIKE :q LIMIT 10"
        endorsement_rows = await session.execute(
            text(endorsement_query), {"q": f"%{q}%"}
        )
        results["endorsements"] = [
            dict(row._mapping) for row in endorsement_rows.fetchall()
        ]

        return results


# Root endpoint
@asynccontextmanager
async def lifespan(app: FastAPI):
    # === Startup logic (if any) ===
    yield
    # === Shutdown logic ===
    client.close()


# Initialize app with lifespan
app = FastAPI(lifespan=lifespan)


# Routers and endpoints
@api_router.get("/")
async def root():
    return {"message": "Insurance Policy Management System API", "version": "1.0.0"}


@api_router.get("/health")
async def health_check():
    return {"status": "healthy", "timestamp": datetime.now(timezone.utc).isoformat()}


# Include your API router
app.include_router(api_router)

# CORS Middleware
app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get("CORS_ORIGINS", "*").split(","),
    allow_methods=["*"],
    allow_headers=["*"],
)

# Logging configuration
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)
logger = logging.getLogger(__name__)
