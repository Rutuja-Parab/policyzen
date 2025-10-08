from fastapi import FastAPI, APIRouter, HTTPException, UploadFile, File, Form, Depends, Query
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field, ConfigDict
from typing import List, Optional, Dict, Any, Union
import uuid
from datetime import datetime, date, timezone
from enum import Enum
import bcrypt
from decimal import Decimal

# Load environment variables
ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

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
    date_fields = ['start_date', 'end_date', 'effective_date', 'audit_date', 'invoice_date', 'credit_date', 'statement_date']
    datetime_fields = ['created_at', 'updated_at', 'uploaded_at', 'action_timestamp', 'changed_at']
    
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
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

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
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

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
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

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
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

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
@api_router.post("/auth/register", response_model=User)
async def register(user_data: UserCreate):
    # Check if user already exists
    existing_user = await db.users.find_one({"email": user_data.email})
    if existing_user:
        raise HTTPException(status_code=400, detail="User already exists")
    
    # Hash password
    password_hash = bcrypt.hashpw(user_data.password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')
    
    # Create user
    user_dict = user_data.model_dump()
    del user_dict['password']
    user_dict['password_hash'] = password_hash
    user_obj = User(**user_dict)
    
    doc = prepare_for_mongo(user_obj.model_dump())
    await db.users.insert_one(doc)
    return user_obj

@api_router.post("/auth/login")
async def login(email: str = Form(...), password: str = Form(...)):
    user = await db.users.find_one({"email": email}, {"_id": 0})
    if not user or not bcrypt.checkpw(password.encode('utf-8'), user['password_hash'].encode('utf-8')):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    user = parse_from_mongo(user)
    return {"message": "Login successful", "user": user}

# Company routes
@api_router.post("/companies", response_model=Company)
async def create_company(company_data: CompanyBase):
    company_obj = Company(**company_data.model_dump())
    doc = prepare_for_mongo(company_obj.model_dump())
    await db.companies.insert_one(doc)
    return company_obj

@api_router.get("/companies", response_model=List[Company])
async def get_companies():
    companies = await db.companies.find({}, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(company) for company in companies]

# Employee routes
@api_router.post("/employees", response_model=Employee)
async def create_employee(employee_data: EmployeeBase):
    # Check if employee code already exists
    existing = await db.employees.find_one({"employee_code": employee_data.employee_code, "company_id": employee_data.company_id})
    if existing:
        raise HTTPException(status_code=400, detail="Employee code already exists")
    
    employee_obj = Employee(**employee_data.model_dump())
    doc = prepare_for_mongo(employee_obj.model_dump())
    await db.employees.insert_one(doc)
    
    # Create corresponding entity
    entity_data = {
        "company_id": employee_data.company_id,
        "type": EntityType.EMPLOYEE,
        "entity_id": employee_obj.id,
        "description": f"Employee: {employee_data.name}"
    }
    entity_obj = Entity(**entity_data)
    entity_doc = prepare_for_mongo(entity_obj.model_dump())
    await db.entities.insert_one(entity_doc)
    
    return employee_obj

@api_router.get("/employees", response_model=List[Employee])
async def get_employees(company_id: Optional[str] = Query(None)):
    query = {"company_id": company_id} if company_id else {}
    employees = await db.employees.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(employee) for employee in employees]

# Student routes
@api_router.post("/students", response_model=Student)
async def create_student(student_data: StudentBase):
    # Check if student ID already exists
    existing = await db.students.find_one({"student_id": student_data.student_id, "company_id": student_data.company_id})
    if existing:
        raise HTTPException(status_code=400, detail="Student ID already exists")
    
    student_obj = Student(**student_data.model_dump())
    doc = prepare_for_mongo(student_obj.model_dump())
    await db.students.insert_one(doc)
    
    # Create corresponding entity
    entity_data = {
        "company_id": student_data.company_id,
        "type": EntityType.STUDENT,
        "entity_id": student_obj.id,
        "description": f"Student: {student_data.name}"
    }
    entity_obj = Entity(**entity_data)
    entity_doc = prepare_for_mongo(entity_obj.model_dump())
    await db.entities.insert_one(entity_doc)
    
    return student_obj

@api_router.get("/students", response_model=List[Student])
async def get_students(company_id: Optional[str] = Query(None)):
    query = {"company_id": company_id} if company_id else {}
    students = await db.students.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(student) for student in students]

# Vessel routes
@api_router.post("/vessels", response_model=Vessel)
async def create_vessel(vessel_data: VesselBase):
    # Check if IMO number already exists
    existing = await db.vessels.find_one({"imo_number": vessel_data.imo_number})
    if existing:
        raise HTTPException(status_code=400, detail="IMO number already exists")
    
    vessel_obj = Vessel(**vessel_data.model_dump())
    doc = prepare_for_mongo(vessel_obj.model_dump())
    await db.vessels.insert_one(doc)
    
    # Create corresponding entity
    entity_data = {
        "company_id": vessel_data.company_id,
        "type": EntityType.SHIP,
        "entity_id": vessel_obj.id,
        "description": f"Vessel: {vessel_data.vessel_name}"
    }
    entity_obj = Entity(**entity_data)
    entity_doc = prepare_for_mongo(entity_obj.model_dump())
    await db.entities.insert_one(entity_doc)
    
    return vessel_obj

@api_router.get("/vessels", response_model=List[Vessel])
async def get_vessels(company_id: Optional[str] = Query(None)):
    query = {"company_id": company_id} if company_id else {}
    vessels = await db.vessels.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(vessel) for vessel in vessels]

# Vehicle routes
@api_router.post("/vehicles", response_model=Vehicle)
async def create_vehicle(vehicle_data: VehicleBase):
    # Check if registration number already exists
    existing = await db.vehicles.find_one({"registration_number": vehicle_data.registration_number})
    if existing:
        raise HTTPException(status_code=400, detail="Registration number already exists")
    
    vehicle_obj = Vehicle(**vehicle_data.model_dump())
    doc = prepare_for_mongo(vehicle_obj.model_dump())
    await db.vehicles.insert_one(doc)
    
    # Create corresponding entity
    entity_data = {
        "company_id": vehicle_data.company_id,
        "type": EntityType.VEHICLE,
        "entity_id": vehicle_obj.id,
        "description": f"Vehicle: {vehicle_data.make} {vehicle_data.model}"
    }
    entity_obj = Entity(**entity_data)
    entity_doc = prepare_for_mongo(entity_obj.model_dump())
    await db.entities.insert_one(entity_doc)
    
    return vehicle_obj

@api_router.get("/vehicles", response_model=List[Vehicle])
async def get_vehicles(company_id: Optional[str] = Query(None)):
    query = {"company_id": company_id} if company_id else {}
    vehicles = await db.vehicles.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(vehicle) for vehicle in vehicles]

# Entity routes
@api_router.get("/entities", response_model=List[Entity])
async def get_entities(company_id: Optional[str] = Query(None), entity_type: Optional[EntityType] = Query(None)):
    query = {}
    if company_id:
        query["company_id"] = company_id
    if entity_type:
        query["type"] = entity_type
    
    entities = await db.entities.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(entity) for entity in entities]

# Policy routes
@api_router.post("/policies", response_model=InsurancePolicy)
async def create_policy(policy_data: InsurancePolicyBase):
    # Check if policy number already exists
    existing = await db.policies.find_one({"policy_number": policy_data.policy_number})
    if existing:
        raise HTTPException(status_code=400, detail="Policy number already exists")
    
    policy_obj = InsurancePolicy(**policy_data.model_dump())
    doc = prepare_for_mongo(policy_obj.model_dump())
    await db.policies.insert_one(doc)
    return policy_obj

@api_router.get("/policies", response_model=List[InsurancePolicy])
async def get_policies(
    entity_id: Optional[str] = Query(None),
    status: Optional[PolicyStatus] = Query(None),
    insurance_type: Optional[InsuranceType] = Query(None)
):
    query = {}
    if entity_id:
        query["entity_id"] = entity_id
    if status:
        query["status"] = status
    if insurance_type:
        query["insurance_type"] = insurance_type
    
    policies = await db.policies.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(policy) for policy in policies]

@api_router.get("/policies/{policy_id}", response_model=InsurancePolicy)
async def get_policy(policy_id: str):
    policy = await db.policies.find_one({"id": policy_id}, {"_id": 0})
    if not policy:
        raise HTTPException(status_code=404, detail="Policy not found")
    return parse_from_mongo(policy)

@api_router.put("/policies/{policy_id}/status")
async def update_policy_status(policy_id: str, status: PolicyStatus):
    result = await db.policies.update_one(
        {"id": policy_id},
        {"$set": {"status": status, "updated_at": datetime.now(timezone.utc).isoformat()}}
    )
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Policy not found")
    return {"message": "Policy status updated"}

# Endorsement routes
@api_router.post("/endorsements", response_model=PolicyEndorsement)
async def create_endorsement(endorsement_data: PolicyEndorsementBase):
    # Check if endorsement number already exists
    existing = await db.endorsements.find_one({"endorsement_number": endorsement_data.endorsement_number})
    if existing:
        raise HTTPException(status_code=400, detail="Endorsement number already exists")
    
    endorsement_obj = PolicyEndorsement(**endorsement_data.model_dump())
    doc = prepare_for_mongo(endorsement_obj.model_dump())
    await db.endorsements.insert_one(doc)
    return endorsement_obj

@api_router.get("/endorsements", response_model=List[PolicyEndorsement])
async def get_endorsements(policy_id: Optional[str] = Query(None)):
    query = {"policy_id": policy_id} if policy_id else {}
    endorsements = await db.endorsements.find(query, {"_id": 0}).to_list(1000)
    return [parse_from_mongo(endorsement) for endorsement in endorsements]

# Dashboard stats
@api_router.get("/dashboard/stats", response_model=DashboardStats)
async def get_dashboard_stats():
    # Get total policies
    total_policies = await db.policies.count_documents({})
    
    # Get active policies
    active_policies = await db.policies.count_documents({"status": "ACTIVE"})
    
    # Get expired policies
    expired_policies = await db.policies.count_documents({"status": "EXPIRED"})
    
    # Get total premium (sum of all active policies)
    pipeline = [
        {"$match": {"status": "ACTIVE"}},
        {"$group": {"_id": None, "total": {"$sum": "$premium_amount"}}}
    ]
    premium_result = await db.policies.aggregate(pipeline).to_list(1)
    total_premium = premium_result[0]["total"] if premium_result else 0.0
    
    # Get total entities
    total_entities = await db.entities.count_documents({})
    
    # Get recent endorsements (last 30 days)
    thirty_days_ago = datetime.now(timezone.utc) - timedelta(days=30)
    recent_endorsements = await db.endorsements.count_documents({
        "created_at": {"$gte": thirty_days_ago.isoformat()}
    })
    
    return DashboardStats(
        total_policies=total_policies,
        active_policies=active_policies,
        expired_policies=expired_policies,
        total_premium=total_premium,
        total_entities=total_entities,
        recent_endorsements=recent_endorsements
    )

# Search endpoint
@api_router.get("/search")
async def search(
    q: str = Query(..., description="Search query"),
    entity_type: Optional[str] = Query(None)
):
    results = {
        "policies": [],
        "entities": [],
        "endorsements": []
    }
    
    # Search policies
    policy_query = {
        "$or": [
            {"policy_number": {"$regex": q, "$options": "i"}},
            {"provider": {"$regex": q, "$options": "i"}}
        ]
    }
    policies = await db.policies.find(policy_query, {"_id": 0}).limit(10).to_list(10)
    results["policies"] = [parse_from_mongo(p) for p in policies]
    
    # Search entities based on type
    if entity_type:
        if entity_type.upper() == "EMPLOYEE":
            employees = await db.employees.find(
                {"$or": [
                    {"name": {"$regex": q, "$options": "i"}},
                    {"employee_code": {"$regex": q, "$options": "i"}}
                ]},
                {"_id": 0}
            ).limit(10).to_list(10)
            results["entities"] = [parse_from_mongo(e) for e in employees]
        elif entity_type.upper() == "STUDENT":
            students = await db.students.find(
                {"$or": [
                    {"name": {"$regex": q, "$options": "i"}},
                    {"student_id": {"$regex": q, "$options": "i"}}
                ]},
                {"_id": 0}
            ).limit(10).to_list(10)
            results["entities"] = [parse_from_mongo(s) for s in students]
        elif entity_type.upper() == "VESSEL":
            vessels = await db.vessels.find(
                {"$or": [
                    {"vessel_name": {"$regex": q, "$options": "i"}},
                    {"imo_number": {"$regex": q, "$options": "i"}}
                ]},
                {"_id": 0}
            ).limit(10).to_list(10)
            results["entities"] = [parse_from_mongo(v) for v in vessels]
        elif entity_type.upper() == "VEHICLE":
            vehicles = await db.vehicles.find(
                {"$or": [
                    {"registration_number": {"$regex": q, "$options": "i"}},
                    {"make": {"$regex": q, "$options": "i"}},
                    {"model": {"$regex": q, "$options": "i"}}
                ]},
                {"_id": 0}
            ).limit(10).to_list(10)
            results["entities"] = [parse_from_mongo(v) for v in vehicles]
    
    # Search endorsements
    endorsement_query = {
        "$or": [
            {"endorsement_number": {"$regex": q, "$options": "i"}},
            {"description": {"$regex": q, "$options": "i"}}
        ]
    }
    endorsements = await db.endorsements.find(endorsement_query, {"_id": 0}).limit(10).to_list(10)
    results["endorsements"] = [parse_from_mongo(e) for e in endorsements]
    
    return results

# Include the router in the main app
app.include_router(api_router)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()

# Root endpoint
@api_router.get("/")
async def root():
    return {"message": "Insurance Policy Management System API", "version": "1.0.0"}

# Health check
@api_router.get("/health")
async def health_check():
    return {"status": "healthy", "timestamp": datetime.now(timezone.utc).isoformat()}