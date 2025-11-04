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
  const [editEntity, setEditEntity] = useState(null);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [viewEntity, setViewEntity] = useState(null);
  const [showViewDialog, setShowViewDialog] = useState(false);

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

    let entityData = null;

    try {
      let endpoint = '';
      entityData = { ...newEntity };

      // Ensure company_id is set - prioritize in this order:
      // 1. Entity data already has company_id (if valid UUID)
      // 2. User's company_id
      // 3. Fetch first company from database
      // 4. Create a default company if none exists
      // 5. Generate a valid UUID as last resort
      
      // Helper function to validate UUID format
      const isValidUUID = (str) => {
        const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
        return uuidRegex.test(str);
      };

      if (!entityData.company_id || entityData.company_id === 'default-company' || !isValidUUID(entityData.company_id)) {
        if (user?.company_id && isValidUUID(user.company_id)) {
          entityData.company_id = user.company_id;
        } else {
          // Try to fetch first company from database
          try {
            const companiesRes = await axios.get(`${API}/companies`);
            if (companiesRes.data && companiesRes.data.length > 0) {
              entityData.company_id = companiesRes.data[0].id;
            } else {
              // Create a default company if none exists
              const newCompany = await axios.post(`${API}/companies`, {
                name: 'Default Company'
              });
              entityData.company_id = newCompany.data.id;
            }
          } catch (companyError) {
            console.error('Error fetching/creating company:', companyError);
            // Generate a valid UUID v4 as last resort
            entityData.company_id = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
              const r = Math.random() * 16 | 0;
              const v = c === 'x' ? r : (r & 0x3 | 0x8);
              return v.toString(16);
            });
          }
        }
      }
      
      console.log('Final entityData.company_id:', entityData.company_id);

      // Add default status if not provided
      if (!entityData.status) {
        entityData.status = 'ACTIVE';
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
      console.error('Error response:', error.response?.data);
      console.error('Entity data being sent:', entityData);
      const errorMessage = error.response?.data?.detail || error.response?.data?.message || 'Failed to add entity';                                                                              
      toast.error(errorMessage);
    } finally {
      setFormLoading(false);
    }
  };

  const handleDeleteEntity = async (entityType, id) => {
    if (!window.confirm('Are you sure you want to delete this item?')) return;
    try {
      const endpoint = `/${entityType}`.replace('employees', 'employees')
        .replace('students', 'students')
        .replace('vessels', 'vessels')
        .replace('vehicles', 'vehicles');
      await axios.delete(`${API}${endpoint}/${id}`);
      toast.success('Deleted successfully');
      fetchEntities();
    } catch (err) {
      console.error('Delete error:', err);
      toast.error('Failed to delete');
    }
  };

  const handleEditEntity = async (e) => {
    e.preventDefault();
    setFormLoading(true);
    try {
      const entityType = activeTab;
      const endpoint = `/${entityType}`;
      await axios.put(`${API}${endpoint}/${editEntity.id}`, editEntity);
      toast.success('Updated successfully');
      setShowEditDialog(false);
      setEditEntity(null);
      fetchEntities();
    } catch (err) {
      console.error('Update error:', err);
      toast.error('Failed to update');
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
                    {entityType === 'vessels' ? entity.vessel_name : entity.name}
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
                  <Button variant="outline" size="sm" className="flex-1" onClick={() => { setViewEntity(entity); setShowViewDialog(true); }}>                   
                    <Eye className="w-4 h-4 mr-1" />
                    View
                  </Button>
                  <Button variant="outline" size="sm" className="flex-1" onClick={() => { setEditEntity(entity); setShowEditDialog(true); }}>                   
                    <Edit2 className="w-4 h-4 mr-1" />
                    Edit
                  </Button>
                  <Button variant="outline" size="sm" className="flex-1 text-red-600" onClick={() => handleDeleteEntity(entityType, entity.id)}>
                    Delete
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
          </div>
        </CardContent>
      </Card>

      {/* Entity Tabs */}
      <Tabs value={activeTab} onValueChange={(value) => {
        setActiveTab(value);
        setNewEntity({});
        setShowAddDialog(false);
      }} className="space-y-6">
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

      {/* Edit dialog */}
      <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Edit {activeTab.slice(0, -1)}</DialogTitle>
          </DialogHeader>
          {editEntity && (
            <form onSubmit={handleEditEntity} className="space-y-6">
              {/* Reuse add form fields by mapping to editEntity state */}
              {activeTab === 'employees' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="employee_code_edit">Employee Code *</Label>
                    <Input id="employee_code_edit" value={editEntity.employee_code || ''} onChange={(e) => setEditEntity({ ...editEntity, employee_code: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="name_edit">Name *</Label>
                    <Input id="name_edit" value={editEntity.name || ''} onChange={(e) => setEditEntity({ ...editEntity, name: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="department_edit">Department</Label>
                    <Input id="department_edit" value={editEntity.department || ''} onChange={(e) => setEditEntity({ ...editEntity, department: e.target.value })} />
                  </div>
                  <div>
                    <Label htmlFor="position_edit">Position</Label>
                    <Input id="position_edit" value={editEntity.position || ''} onChange={(e) => setEditEntity({ ...editEntity, position: e.target.value })} />
                  </div>
                </div>
              )}

              {activeTab === 'students' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="student_id_edit">Student ID *</Label>
                    <Input id="student_id_edit" value={editEntity.student_id || ''} onChange={(e) => setEditEntity({ ...editEntity, student_id: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="name_student_edit">Name *</Label>
                    <Input id="name_student_edit" value={editEntity.name || ''} onChange={(e) => setEditEntity({ ...editEntity, name: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="course_edit">Course</Label>
                    <Input id="course_edit" value={editEntity.course || ''} onChange={(e) => setEditEntity({ ...editEntity, course: e.target.value })} />
                  </div>
                  <div>
                    <Label htmlFor="year_of_study_edit">Year of Study</Label>
                    <Input id="year_of_study_edit" type="number" value={editEntity.year_of_study || ''} onChange={(e) => setEditEntity({ ...editEntity, year_of_study: parseInt(e.target.value) })} />
                  </div>
                </div>
              )}

              {activeTab === 'vessels' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="vessel_name_edit">Vessel Name *</Label>
                    <Input id="vessel_name_edit" value={editEntity.vessel_name || ''} onChange={(e) => setEditEntity({ ...editEntity, vessel_name: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="imo_number_edit">IMO Number *</Label>
                    <Input id="imo_number_edit" value={editEntity.imo_number || ''} onChange={(e) => setEditEntity({ ...editEntity, imo_number: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="vessel_type_edit">Vessel Type</Label>
                    <Input id="vessel_type_edit" value={editEntity.vessel_type || ''} onChange={(e) => setEditEntity({ ...editEntity, vessel_type: e.target.value })} />
                  </div>
                  <div>
                    <Label htmlFor="flag_edit">Flag</Label>
                    <Input id="flag_edit" value={editEntity.flag || ''} onChange={(e) => setEditEntity({ ...editEntity, flag: e.target.value })} />
                  </div>
                </div>
              )}

              {activeTab === 'vehicles' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="registration_number_edit">Registration Number *</Label>
                    <Input id="registration_number_edit" value={editEntity.registration_number || ''} onChange={(e) => setEditEntity({ ...editEntity, registration_number: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="make_edit">Make *</Label>
                    <Input id="make_edit" value={editEntity.make || ''} onChange={(e) => setEditEntity({ ...editEntity, make: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="model_edit">Model *</Label>
                    <Input id="model_edit" value={editEntity.model || ''} onChange={(e) => setEditEntity({ ...editEntity, model: e.target.value })} required />
                  </div>
                  <div>
                    <Label htmlFor="year_edit">Year *</Label>
                    <Input id="year_edit" type="number" value={editEntity.year || ''} onChange={(e) => setEditEntity({ ...editEntity, year: parseInt(e.target.value) })} required />
                  </div>
                </div>
              )}

              <div className="flex justify-end space-x-4">
                <Button type="button" variant="outline" onClick={() => setShowEditDialog(false)}>Cancel</Button>
                <Button type="submit" disabled={formLoading} className="bg-gradient-to-r from-blue-600 to-indigo-600">{formLoading ? 'Saving...' : 'Save Changes'}</Button>
              </div>
            </form>
          )}
        </DialogContent>
      </Dialog>

      {/* View Entity Dialog */}
      <Dialog open={showViewDialog} onOpenChange={setShowViewDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>View {activeTab.slice(0, -1)}</DialogTitle>
          </DialogHeader>
          {viewEntity && (
            <div className="space-y-4">
              {activeTab === 'employees' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-gray-600">Employee Code</Label>
                    <p className="font-medium">{viewEntity.employee_code || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Name</Label>
                    <p className="font-medium">{viewEntity.name || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Department</Label>
                    <p className="font-medium">{viewEntity.department || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Position</Label>
                    <p className="font-medium">{viewEntity.position || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Status</Label>
                    <div className="mt-1">{getStatusBadge(viewEntity.status)}</div>
                  </div>
                </div>
              )}

              {activeTab === 'students' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-gray-600">Student ID</Label>
                    <p className="font-medium">{viewEntity.student_id || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Name</Label>
                    <p className="font-medium">{viewEntity.name || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Course</Label>
                    <p className="font-medium">{viewEntity.course || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Year of Study</Label>
                    <p className="font-medium">{viewEntity.year_of_study || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Status</Label>
                    <div className="mt-1">{getStatusBadge(viewEntity.status)}</div>
                  </div>
                </div>
              )}

              {activeTab === 'vessels' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-gray-600">Vessel Name</Label>
                    <p className="font-medium">{viewEntity.vessel_name || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">IMO Number</Label>
                    <p className="font-medium">{viewEntity.imo_number || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Vessel Type</Label>
                    <p className="font-medium">{viewEntity.vessel_type || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Flag</Label>
                    <p className="font-medium">{viewEntity.flag || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Status</Label>
                    <div className="mt-1">{getStatusBadge(viewEntity.status)}</div>
                  </div>
                </div>
              )}

              {activeTab === 'vehicles' && (
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-gray-600">Registration Number</Label>
                    <p className="font-medium">{viewEntity.registration_number || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Make</Label>
                    <p className="font-medium">{viewEntity.make || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Model</Label>
                    <p className="font-medium">{viewEntity.model || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Year</Label>
                    <p className="font-medium">{viewEntity.year || 'N/A'}</p>
                  </div>
                  <div>
                    <Label className="text-gray-600">Status</Label>
                    <div className="mt-1">{getStatusBadge(viewEntity.status)}</div>
                  </div>
                </div>
              )}

              <div className="flex justify-end pt-4">
                <Button variant="outline" onClick={() => setShowViewDialog(false)}>
                  Close
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default EntityManagement;