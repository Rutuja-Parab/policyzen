import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { 
  Edit, 
  Plus, 
  Search, 
  Eye, 
  FileText,
  Calendar,
  User,
  AlertCircle
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const EndorsementManagement = ({ user }) => {
  const [endorsements, setEndorsements] = useState([]);
  const [policies, setPolicies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [newEndorsement, setNewEndorsement] = useState({
    policy_id: '',
    endorsement_number: '',
    description: '',
    effective_date: '',
    created_by: user?.id || 'default-user'
  });
  const [formLoading, setFormLoading] = useState(false);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [endorsementsRes, policiesRes] = await Promise.all([
        axios.get(`${API}/endorsements`),
        axios.get(`${API}/policies`)
      ]);
      
      setEndorsements(endorsementsRes.data);
      setPolicies(policiesRes.data);
    } catch (error) {
      console.error('Error fetching data:', error);
      toast.error('Failed to load endorsements');
    } finally {
      setLoading(false);
    }
  };

  const handleAddEndorsement = async (e) => {
    e.preventDefault();
    setFormLoading(true);
    
    try {
      await axios.post(`${API}/endorsements`, newEndorsement);
      
      toast.success('Endorsement created successfully');
      setShowAddDialog(false);
      setNewEndorsement({
        policy_id: '',
        endorsement_number: '',
        description: '',
        effective_date: '',
        created_by: user?.id || 'default-user'
      });
      fetchData();
    } catch (error) {
      console.error('Error adding endorsement:', error);
      const errorMessage = error.response?.data?.detail || 'Failed to create endorsement';
      toast.error(errorMessage);
    } finally {
      setFormLoading(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getPolicyNumber = (policyId) => {
    const policy = policies.find(p => p.id === policyId);
    return policy ? policy.policy_number : 'Unknown Policy';
  };

  const filteredEndorsements = endorsements.filter(endorsement => {
    const policyNumber = getPolicyNumber(endorsement.policy_id);
    return (
      endorsement.endorsement_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
      endorsement.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
      policyNumber.toLowerCase().includes(searchTerm.toLowerCase())
    );
  });

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="space-y-2">
            <div className="h-8 w-64 bg-gray-200 rounded animate-pulse"></div>
            <div className="h-4 w-96 bg-gray-200 rounded animate-pulse"></div>
          </div>
          <div className="h-10 w-32 bg-gray-200 rounded animate-pulse"></div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="space-y-4">
                  <div className="h-4 w-3/4 bg-gray-200 rounded"></div>
                  <div className="h-4 w-1/2 bg-gray-200 rounded"></div>
                  <div className="h-4 w-2/3 bg-gray-200 rounded"></div>
                  <div className="h-4 w-full bg-gray-200 rounded"></div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6" data-testid="endorsement-management">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Endorsement Management</h1>
          <p className="text-gray-600 mt-1">Manage policy modifications and endorsements</p>
        </div>
        
        <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
          <DialogTrigger asChild>
            <Button className="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700" data-testid="add-endorsement-btn">
              <Plus className="w-4 h-4 mr-2" />
              Create Endorsement
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Create New Endorsement</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleAddEndorsement} className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div className="col-span-2">
                  <Label htmlFor="policy_id">Policy *</Label>
                  <Select value={newEndorsement.policy_id} onValueChange={(value) => setNewEndorsement({ ...newEndorsement, policy_id: value })}>
                    <SelectTrigger data-testid="endorsement-policy-select">
                      <SelectValue placeholder="Select policy" />
                    </SelectTrigger>
                    <SelectContent>
                      {policies.map(policy => (
                        <SelectItem key={policy.id} value={policy.id}>
                          {policy.policy_number} - {policy.insurance_type}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                
                <div>
                  <Label htmlFor="endorsement_number">Endorsement Number *</Label>
                  <Input
                    id="endorsement_number"
                    value={newEndorsement.endorsement_number}
                    onChange={(e) => setNewEndorsement({ ...newEndorsement, endorsement_number: e.target.value })}
                    placeholder="END001"
                    required
                    data-testid="endorsement-number-input"
                  />
                </div>
                
                <div>
                  <Label htmlFor="effective_date">Effective Date *</Label>
                  <Input
                    id="effective_date"
                    type="date"
                    value={newEndorsement.effective_date}
                    onChange={(e) => setNewEndorsement({ ...newEndorsement, effective_date: e.target.value })}
                    required
                    data-testid="endorsement-date-input"
                  />
                </div>
                
                <div className="col-span-2">
                  <Label htmlFor="description">Description *</Label>
                  <Textarea
                    id="description"
                    value={newEndorsement.description}
                    onChange={(e) => setNewEndorsement({ ...newEndorsement, description: e.target.value })}
                    placeholder="Describe the policy modification..."
                    rows={4}
                    required
                    data-testid="endorsement-description-input"
                  />
                </div>
              </div>
              
              <div className="flex justify-end space-x-4">
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => setShowAddDialog(false)}
                  data-testid="cancel-endorsement-btn"
                >
                  Cancel
                </Button>
                <Button 
                  type="submit" 
                  disabled={formLoading}
                  className="bg-gradient-to-r from-purple-600 to-indigo-600"
                  data-testid="save-endorsement-btn"
                >
                  {formLoading ? 'Creating...' : 'Create Endorsement'}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Search */}
      <Card className="border-0 shadow-sm">
        <CardContent className="p-6">
          <div className="relative max-w-md">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input
              placeholder="Search endorsements..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
              data-testid="endorsement-search-input"
            />
          </div>
        </CardContent>
      </Card>

      {/* Endorsements Grid */}
      {filteredEndorsements.length === 0 ? (
        <div className="text-center py-12">
          <Edit className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No endorsements found</h3>
          <p className="text-gray-500 mb-4">
            {searchTerm 
              ? `No results for "${searchTerm}"`
              : 'Create your first policy endorsement'
            }
          </p>
          <Button onClick={() => setShowAddDialog(true)} data-testid="create-first-endorsement-btn">
            <Plus className="w-4 h-4 mr-2" />
            Create Endorsement
          </Button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredEndorsements.map((endorsement) => (
            <Card key={endorsement.id} className="hover:shadow-lg transition-all duration-200 border-0 bg-gradient-to-br from-white to-gray-50">
              <CardContent className="p-6">
                <div className="space-y-4">
                  {/* Header */}
                  <div className="flex items-start justify-between">
                    <div className="space-y-1">
                      <h3 className="font-semibold text-gray-900" data-testid={`endorsement-number-${endorsement.id}`}>
                        {endorsement.endorsement_number}
                      </h3>
                      <p className="text-sm text-gray-600">
                        Policy: {getPolicyNumber(endorsement.policy_id)}
                      </p>
                    </div>
                    <div className="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                      <Edit className="w-5 h-5 text-purple-600" />
                    </div>
                  </div>
                  
                  {/* Description */}
                  <div className="space-y-2">
                    <p className="text-sm text-gray-700 line-clamp-3">
                      {endorsement.description}
                    </p>
                  </div>
                  
                  {/* Metadata */}
                  <div className="space-y-2 pt-2 border-t border-gray-100">
                    <div className="flex items-center space-x-2 text-sm text-gray-600">
                      <Calendar className="w-4 h-4" />
                      <span>Effective: {formatDate(endorsement.effective_date)}</span>
                    </div>
                    
                    <div className="flex items-center space-x-2 text-sm text-gray-600">
                      <User className="w-4 h-4" />
                      <span>Created: {formatDate(endorsement.created_at)}</span>
                    </div>
                  </div>
                  
                  {/* Actions */}
                  <div className="flex space-x-2 pt-2">
                    <Button variant="outline" size="sm" className="flex-1">
                      <Eye className="w-4 h-4 mr-1" />
                      View
                    </Button>
                    <Button variant="outline" size="sm" className="flex-1">
                      <Edit className="w-4 h-4 mr-1" />
                      Edit
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card className="border-0 shadow-sm">
          <CardContent className="p-6">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <FileText className="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-gray-900">{endorsements.length}</p>
                <p className="text-sm text-gray-600">Total Endorsements</p>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card className="border-0 shadow-sm">
          <CardContent className="p-6">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <Calendar className="w-6 h-6 text-green-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-gray-900">
                  {endorsements.filter(e => {
                    const effectiveDate = new Date(e.effective_date);
                    const thirtyDaysAgo = new Date();
                    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                    return effectiveDate >= thirtyDaysAgo;
                  }).length}
                </p>
                <p className="text-sm text-gray-600">This Month</p>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card className="border-0 shadow-sm">
          <CardContent className="p-6">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <AlertCircle className="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <p className="text-2xl font-bold text-gray-900">
                  {policies.length}
                </p>
                <p className="text-sm text-gray-600">Active Policies</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default EndorsementManagement;