import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  Users, 
  Plus, 
  Search, 
  Edit2, 
  Eye, 
  UserCheck, 
  Car, 
  Ship, 
  GraduationCap,
  Building,
  Filter
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const EntityManagement = ({ user }) => {
  const [entities, setEntities] = useState({
    employees: [],
    students: [],
    vessels: [],
    vehicles: []
  });
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('employees');
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [newEntity, setNewEntity] = useState({});
  const [formLoading, setFormLoading] = useState(false);

  useEffect(() => {
    fetchEntities();
  }, []);

  const fetchEntities = async () => {
    try {
      setLoading(true);
      
      const [employeesRes, studentsRes, vesselsRes, vehiclesRes] = await Promise.all([
        axios.get(`${API}/employees`),
        axios.get(`${API}/students`),
        axios.get(`${API}/vessels`),
        axios.get(`${API}/vehicles`)
      ]);
      
      setEntities({
        employees: employeesRes.data,
        students: studentsRes.data,
        vessels: vesselsRes.data,
        vehicles: vehiclesRes.data
      });
    } catch (error) {
      console.error('Error fetching entities:', error);
      toast.error('Failed to load entities');
    } finally {
      setLoading(false);
    }
  };

  const handleAddEntity = async (e) => {
    e.preventDefault();
    setFormLoading(true);
    
    try {
      let endpoint = '';
      let entityData = { ...newEntity };
      
      // Add default company_id if not provided
      if (!entityData.company_id) {
        entityData.company_id = 'default-company';
      }
      
      switch (activeTab) {
        case 'employees':
          endpoint = '/employees';
          break;
        case 'students':
          endpoint = '/students';
          break;
        case 'vessels':
          endpoint = '/vessels';
          break;
        case 'vehicles':
          endpoint = '/vehicles';
          break;
        default:
          throw new Error('Invalid entity type');
      }
      
      await axios.post(`${API}${endpoint}`, entityData);
      
      toast.success(`${activeTab.slice(0, -1)} added successfully`);
      setShowAddDialog(false);
      setNewEntity({});
      fetchEntities();
    } catch (error) {
      console.error('Error adding entity:', error);
      const errorMessage = error.response?.data?.detail || 'Failed to add entity';
      toast.error(errorMessage);
    } finally {
      setFormLoading(false);
    }
  };

  const getStatusBadge = (status) => {
    return (
      <Badge 
        variant={status === 'ACTIVE' ? 'default' : 'secondary'}
        className={status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}
      >
        {status}
      </Badge>
    );
  };

  const getEntityIcon = (type) => {
    switch (type) {
      case 'employees': return UserCheck;
      case 'students': return GraduationCap;
      case 'vessels': return Ship;
      case 'vehicles': return Car;
      default: return Users;
    }
  };

  const getEntityColor = (type) => {
    switch (type) {
      case 'employees': return 'text-blue-600 bg-blue-100';
      case 'students': return 'text-emerald-600 bg-emerald-100';
      case 'vessels': return 'text-purple-600 bg-purple-100';
      case 'vehicles': return 'text-orange-600 bg-orange-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const renderEntityForm = () => {
    switch (activeTab) {
      case 'employees':
        return (
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="employee_code">Employee Code *</Label>
              <Input
                id="employee_code"
                value={newEntity.employee_code || ''}
                onChange={(e) => setNewEntity({ ...newEntity, employee_code: e.target.value })}
                placeholder="EMP001"
                required
                data-testid="employee-code-input"
              />
            </div>
            <div>
              <Label htmlFor="name">Name *</Label>
              <Input
                id="name"
                value={newEntity.name || ''}
                onChange={(e) => setNewEntity({ ...newEntity, name: e.target.value })}
                placeholder="John Doe"
                required
                data-testid="employee-name-input"
              />
            </div>
            <div>
              <Label htmlFor="department">Department</Label>
              <Input
                id="department"
                value={newEntity.department || ''}
                onChange={(e) => setNewEntity({ ...newEntity, department: e.target.value })}
                placeholder="IT Department"
                data-testid="employee-department-input"
              />
            </div>
            <div>
              <Label htmlFor="position">Position</Label>
              <Input
                id="position"
                value={newEntity.position || ''}
                onChange={(e) => setNewEntity({ ...newEntity, position: e.target.value })}
                placeholder="Software Engineer"
                data-testid="employee-position-input"
              />
            </div>
          </div>
        );
      
      case 'students':
        return (
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="student_id">Student ID *</Label>
              <Input
                id="student_id"
                value={newEntity.student_id || ''}
                onChange={(e) => setNewEntity({ ...newEntity, student_id: e.target.value })}
                placeholder="STU001"
                required
                data-testid="student-id-input"
              />
            </div>
            <div>
              <Label htmlFor="name">Name *</Label>
              <Input
                id="name"
                value={newEntity.name || ''}
                onChange={(e) => setNewEntity({ ...newEntity, name: e.target.value })}
                placeholder="Jane Smith"
                required
                data-testid="student-name-input"
              />
            </div>
            <div>
              <Label htmlFor="course">Course</Label>
              <Input
                id="course"
                value={newEntity.course || ''}
                onChange={(e) => setNewEntity({ ...newEntity, course: e.target.value })}
                placeholder="Computer Science"
                data-testid="student-course-input"
              />
            </div>
            <div>
              <Label htmlFor="year_of_study">Year of Study</Label>
              <Input
                id="year_of_study"
                type="number"
                value={newEntity.year_of_study || ''}
                onChange={(e) => setNewEntity({ ...newEntity, year_of_study: parseInt(e.target.value) })}
                placeholder="1"
                min="1"
                max="7"
                data-testid="student-year-input"
              />
            </div>
          </div>
        );
      
      case 'vessels':
        return (
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="vessel_name">Vessel Name *</Label>
              <Input
                id="vessel_name"
                value={newEntity.vessel_name || ''}
                onChange={(e) => setNewEntity({ ...newEntity, vessel_name: e.target.value })}
                placeholder="Ocean Explorer"
                required
                data-testid="vessel-name-input"
              />
            </div>
            <div>
              <Label htmlFor="imo_number">IMO Number *</Label>
              <Input
                id="imo_number"
                value={newEntity.imo_number || ''}
                onChange={(e) => setNewEntity({ ...newEntity, imo_number: e.target.value })}
                placeholder="1234567"
                required
                data-testid="vessel-imo-input"
              />
            </div>
            <div>
              <Label htmlFor="vessel_type">Vessel Type</Label>
              <Input
                id="vessel_type"
                value={newEntity.vessel_type || ''}
                onChange={(e) => setNewEntity({ ...newEntity, vessel_type: e.target.value })}
                placeholder="Cargo Ship"
                data-testid="vessel-type-input"
              />
            </div>
            <div>
              <Label htmlFor="flag">Flag</Label>
              <Input
                id="flag"
                value={newEntity.flag || ''}
                onChange={(e) => setNewEntity({ ...newEntity, flag: e.target.value })}
                placeholder="USA"
                data-testid="vessel-flag-input"
              />
            </div>
          </div>
        );
      
      case 'vehicles':
        return (
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="registration_number">Registration Number *</Label>
              <Input
                id="registration_number"
                value={newEntity.registration_number || ''}
                onChange={(e) => setNewEntity({ ...newEntity, registration_number: e.target.value })}
                placeholder="ABC123"
                required
                data-testid="vehicle-registration-input"
              />
            </div>
            <div>
              <Label htmlFor="make">Make *</Label>
              <Input
                id="make"
                value={newEntity.make || ''}
                onChange={(e) => setNewEntity({ ...newEntity, make: e.target.value })}
                placeholder="Toyota"
                required
                data-testid="vehicle-make-input"
              />
            </div>
            <div>
              <Label htmlFor="model">Model *</Label>
              <Input
                id="model"
                value={newEntity.model || ''}
                onChange={(e) => setNewEntity({ ...newEntity, model: e.target.value })}
                placeholder="Camry"
                required
                data-testid="vehicle-model-input"
              />
            </div>
            <div>
              <Label htmlFor="year">Year *</Label>
              <Input
                id="year"
                type="number"
                value={newEntity.year || ''}
                onChange={(e) => setNewEntity({ ...newEntity, year: parseInt(e.target.value) })}
                placeholder="2023"
                min="1900"
                max="2030"
                required
                data-testid="vehicle-year-input"
              />
            </div>
          </div>
        );
      
      default:
        return null;
    }
  };

  const renderEntityList = (entityType) => {
    const entityList = entities[entityType] || [];
    const Icon = getEntityIcon(entityType);
    
    const filteredEntities = entityList.filter(entity => {
      const searchFields = {
        employees: [entity.name, entity.employee_code, entity.department],
        students: [entity.name, entity.student_id, entity.course],
        vessels: [entity.vessel_name, entity.imo_number, entity.vessel_type],
        vehicles: [entity.registration_number, entity.make, entity.model]
      };
      
      return searchFields[entityType].some(field => 
        field?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    });

    if (loading) {
      return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {[1, 2, 3].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="space-y-4">
                  <div className="h-4 w-3/4 bg-gray-200 rounded"></div>
                  <div className="h-4 w-1/2 bg-gray-200 rounded"></div>
                  <div className="h-4 w-2/3 bg-gray-200 rounded"></div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      );
    }

    if (filteredEntities.length === 0) {
      return (
        <div className="text-center py-12">
          <Icon className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No {entityType} found</h3>
          <p className="text-gray-500 mb-4">
            {searchTerm ? `No results for "${searchTerm}"` : `Start by adding your first ${entityType.slice(0, -1)}`}
          </p>
          <Button onClick={() => setShowAddDialog(true)} data-testid={`add-${entityType}-btn`}>
            <Plus className="w-4 h-4 mr-2" />
            Add {entityType.slice(0, -1)}
          </Button>
        </div>
      );
    }

    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredEntities.map((entity) => {
          const entityKey = entityType === 'employees' ? entity.employee_code :
                           entityType === 'students' ? entity.student_id :
                           entityType === 'vessels' ? entity.imo_number :
                           entity.registration_number;
          
          return (
            <Card key={entity.id} className="hover:shadow-lg transition-all duration-200 border-0 bg-gradient-to-br from-white to-gray-50">
              <CardContent className="p-6">
                <div className="flex items-start justify-between mb-4">
                  <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${getEntityColor(entityType)}`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  {getStatusBadge(entity.status)}
                </div>
                
                <div className="space-y-2">
                  <h3 className="font-semibold text-gray-900 text-lg" data-testid={`entity-name-${entity.id}`}>
                    {entity.name}
                  </h3>
                  <p className="text-sm text-gray-600">
                    {entityType === 'employees' && `${entity.employee_code} • ${entity.department || 'N/A'}`}
                    {entityType === 'students' && `${entity.student_id} • ${entity.course || 'N/A'}`}
                    {entityType === 'vessels' && `IMO: ${entity.imo_number} • ${entity.vessel_type || 'N/A'}`}
                    {entityType === 'vehicles' && `${entity.registration_number} • ${entity.make} ${entity.model}`}
                  </p>
                  
                  {entityType === 'employees' && entity.position && (
                    <p className="text-xs text-gray-500">{entity.position}</p>
                  )}
                  {entityType === 'students' && entity.year_of_study && (
                    <p className="text-xs text-gray-500">Year {entity.year_of_study}</p>
                  )}
                  {entityType === 'vessels' && entity.flag && (
                    <p className="text-xs text-gray-500">Flag: {entity.flag}</p>
                  )}
                  {entityType === 'vehicles' && entity.year && (
                    <p className="text-xs text-gray-500">{entity.year}</p>
                  )}
                </div>
                
                <div className="flex space-x-2 mt-4 pt-4 border-t border-gray-100">
                  <Button variant="outline" size="sm" className="flex-1">
                    <Eye className="w-4 h-4 mr-1" />
                    View
                  </Button>
                  <Button variant="outline" size="sm" className="flex-1">
                    <Edit2 className="w-4 h-4 mr-1" />
                    Edit
                  </Button>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>
    );
  };

  const entityTabs = [
    { value: 'employees', label: 'Employees', icon: UserCheck, count: entities.employees.length },
    { value: 'students', label: 'Students', icon: GraduationCap, count: entities.students.length },
    { value: 'vessels', label: 'Vessels', icon: Ship, count: entities.vessels.length },
    { value: 'vehicles', label: 'Vehicles', icon: Car, count: entities.vehicles.length }
  ];

  return (
    <div className="space-y-6" data-testid="entity-management">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Entity Management</h1>
          <p className="text-gray-600 mt-1">Manage employees, students, vessels, and vehicles</p>
        </div>
        
        <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
          <DialogTrigger asChild>
            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700" data-testid="add-entity-btn">
              <Plus className="w-4 h-4 mr-2" />
              Add {activeTab.slice(0, -1)}
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Add New {activeTab.slice(0, -1)}</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleAddEntity} className="space-y-6">
              {renderEntityForm()}
              <div className="flex justify-end space-x-4">
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => setShowAddDialog(false)}
                  data-testid="cancel-add-entity-btn"
                >
                  Cancel
                </Button>
                <Button 
                  type="submit" 
                  disabled={formLoading}
                  className="bg-gradient-to-r from-blue-600 to-indigo-600"
                  data-testid="save-entity-btn"
                >
                  {formLoading ? 'Adding...' : `Add ${activeTab.slice(0, -1)}`}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Search and Filter */}
      <Card className="border-0 shadow-sm">
        <CardContent className="p-6">
          <div className="flex items-center space-x-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                placeholder="Search entities..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
                data-testid="entity-search-input"
              />
            </div>
            <Button variant="outline" size="sm">
              <Filter className="w-4 h-4 mr-2" />
              Filter
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Entity Tabs */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-4 h-auto p-1 bg-gray-100">
          {entityTabs.map((tab) => {
            const Icon = tab.icon;
            return (
              <TabsTrigger 
                key={tab.value} 
                value={tab.value} 
                className="data-[state=active]:bg-white data-[state=active]:shadow-sm p-4 text-left"
                data-testid={`tab-${tab.value}`}
              >
                <div className="flex items-center space-x-2">
                  <Icon className="w-4 h-4" />
                  <div>
                    <div className="font-medium">{tab.label}</div>
                    <div className="text-xs text-gray-500">{tab.count} total</div>
                  </div>
                </div>
              </TabsTrigger>
            );
          })}
        </TabsList>

        {entityTabs.map((tab) => (
          <TabsContent key={tab.value} value={tab.value} className="space-y-6">
            {renderEntityList(tab.value)}
          </TabsContent>
        ))}
      </Tabs>
    </div>
  );
};

export default EntityManagement;