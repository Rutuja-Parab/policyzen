import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { useNavigate } from 'react-router-dom';
import {
  FileText,
  Plus,
  Search,
  Edit2,
  Eye,
  Filter,
  Calendar,
  DollarSign,
  Shield,
  AlertTriangle,
  CheckCircle,
  XCircle,
  Clock
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const PolicyManagement = ({ user }) => {
  const navigate = useNavigate();
  const [policies, setPolicies] = useState([]);
  const [entities, setEntities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState('ALL');
  const [filterType, setFilterType] = useState('ALL');
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [viewPolicy, setViewPolicy] = useState(null);
  const [showViewDialog, setShowViewDialog] = useState(false);
  const [newPolicy, setNewPolicy] = useState({
    entity_id: '',
    policy_number: '',
    insurance_type: 'HEALTH',
    provider: '',
    start_date: '',
    end_date: '',
    sum_insured: '',
    premium_amount: '',
    created_by: user?.id || 'default-user',
    status: 'ACTIVE'
  });
  const [formLoading, setFormLoading] = useState(false);
  const [editPolicy, setEditPolicy] = useState(null);
  const [showEditDialog, setShowEditDialog] = useState(false);

  const insuranceTypes = [
    { value: 'HEALTH', label: 'Health Insurance', color: 'bg-green-100 text-green-800' },
    { value: 'ACCIDENT', label: 'Accident Insurance', color: 'bg-red-100 text-red-800' },
    { value: 'PROPERTY', label: 'Property Insurance', color: 'bg-blue-100 text-blue-800' },
    { value: 'VEHICLE', label: 'Vehicle Insurance', color: 'bg-orange-100 text-orange-800' },
    { value: 'MARINE', label: 'Marine Insurance', color: 'bg-purple-100 text-purple-800' }
  ];

  const policyStatuses = [
    { value: 'ACTIVE', label: 'Active', icon: CheckCircle, color: 'text-green-600' },
    { value: 'EXPIRED', label: 'Expired', icon: XCircle, color: 'text-red-600' },
    { value: 'UNDER_REVIEW', label: 'Under Review', icon: Clock, color: 'text-yellow-600' },
    { value: 'CANCELLED', label: 'Cancelled', icon: XCircle, color: 'text-gray-600' }
  ];

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [policiesRes, entitiesRes] = await Promise.all([
        axios.get(`${API}/policies`),
        axios.get(`${API}/entities`)
      ]);

      setPolicies(policiesRes.data);
      setEntities(entitiesRes.data);

      // Fetch expiring policies separately; do not fail the whole load if this is unavailable
      try {
        const expiringRes = await axios.get(`${API}/policies/expiring`, { params: { days: 30 } });
        if (expiringRes.data?.length) {
          toast.warning(`${expiringRes.data.length} policy(ies) expiring within 30 days`);
        }
      } catch {
        // ignore; optional feature
      }
    } catch (error) {
      console.error('Error fetching data:', error);
      toast.error('Failed to load policies');
    } finally {
      setLoading(false);
    }
  };

  const handleAddPolicy = async (e) => {
    e.preventDefault();
    setFormLoading(true);

    try {
      const payload = {
        ...newPolicy,
        sum_insured: parseFloat(newPolicy.sum_insured || 0),
        premium_amount: parseFloat(newPolicy.premium_amount || 0),
      };

      await axios.post(`${API}/policies`, payload);

      toast.success('Policy created successfully');
      setShowAddDialog(false);
      setNewPolicy({
        entity_id: '',
        policy_number: '',
        insurance_type: 'HEALTH',
        provider: '',
        start_date: '',
        end_date: '',
        sum_insured: '',
        premium_amount: '',
        created_by: user?.id || 'default-user',
        status: 'ACTIVE'
      });
      fetchData();
    } catch (error) {
      console.error('Error adding policy:', error);
      const errorMessage = error.response?.data?.detail || 'Failed to create policy';
      toast.error(errorMessage);
    } finally {
      setFormLoading(false);
    }
  };

  const handleUpdatePolicy = async (e) => {
    e.preventDefault();
    setFormLoading(true);

    try {
      const payload = {
        ...editPolicy,
        sum_insured: parseFloat(editPolicy.sum_insured || 0),
        premium_amount: parseFloat(editPolicy.premium_amount || 0),
      };

      await axios.put(`${API}/policies/${editPolicy.id}`, payload);

      toast.success('Policy updated successfully');
      setShowEditDialog(false);
      setEditPolicy(null);
      fetchData();
    } catch (error) {
      console.error('Error updating policy:', error);
      toast.error(error.response?.data?.detail || 'Failed to update policy');
    } finally {
      setFormLoading(false);
    }
  };

  const handleDeletePolicy = async (policyId) => {
    if (window.confirm('Are you sure you want to delete this policy?')) {
      try {
        await axios.delete(`${API}/policies/${policyId}`);
        toast.success('Policy deleted successfully');
        fetchData();
      } catch (error) {
        console.error('Error deleting policy:', error);
        toast.error('Failed to delete policy');
      }
    }
  };

  const updatePolicyStatus = async (policyId, newStatus) => {
    try {
      await axios.put(`${API}/policies/${policyId}/status`, null, {
        params: { status: newStatus }
      });

      toast.success('Policy status updated');
      fetchData();
    } catch (error) {
      console.error('Error updating policy status:', error);
      toast.error('Failed to update policy status');
    }
  };

  const getStatusBadge = (status) => {
    const statusConfig = policyStatuses.find(s => s.value === status) || policyStatuses[0];
    const Icon = statusConfig.icon;

    return (
      <Badge
        className={`${statusConfig.color === 'text-green-600' ? 'bg-green-100 text-green-800' :
          statusConfig.color === 'text-red-600' ? 'bg-red-100 text-red-800' :
            statusConfig.color === 'text-yellow-600' ? 'bg-yellow-100 text-yellow-800' :
              'bg-gray-100 text-gray-800'} border-0`}
      >
        <Icon className="w-3 h-3 mr-1" />
        {statusConfig.label}
      </Badge>
    );
  };

  const getTypeBadge = (type) => {
    const typeConfig = insuranceTypes.find(t => t.value === type) || insuranceTypes[0];
    return (
      <Badge className={`${typeConfig.color} border-0`}>
        {typeConfig.label}
      </Badge>
    );
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const isExpiringSoon = (endDate) => {
    const today = new Date();
    const expiry = new Date(endDate);
    const daysDiff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    return daysDiff <= 30 && daysDiff > 0;
  };

  const filteredPolicies = policies.filter(policy => {
    const matchesSearch =
      policy.policy_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
      policy.provider.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus = filterStatus === 'ALL' || policy.status === filterStatus;
    const matchesType = filterType === 'ALL' || policy.insurance_type === filterType;

    return matchesSearch && matchesStatus && matchesType;
  });

  const getEntityName = (entityId) => {
    const entity = entities.find(e => e.id === entityId);
    return entity ? entity.description : 'Unknown Entity';
  };

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
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6" data-testid="policy-management">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Policy Management</h1>
          <p className="text-gray-600 mt-1">Create and manage insurance policies across all entities</p>
        </div>

        <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
          <DialogTrigger asChild>
            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700" data-testid="add-policy-btn">
              <Plus className="w-4 h-4 mr-2" />
              Create Policy
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Create New Policy</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleAddPolicy} className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="entity_id">Entity *</Label>
                  <Select value={newPolicy.entity_id} onValueChange={(value) => setNewPolicy({ ...newPolicy, entity_id: value })}>
                    <SelectTrigger data-testid="policy-entity-select">
                      <SelectValue placeholder="Select entity" />
                    </SelectTrigger>
                    <SelectContent>
                      {entities.map(entity => (
                        <SelectItem key={entity.id} value={entity.id}>
                          {entity.description}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="policy_number">Policy Number *</Label>
                  <Input
                    id="policy_number"
                    value={newPolicy.policy_number}
                    onChange={(e) => setNewPolicy({ ...newPolicy, policy_number: e.target.value })}
                    placeholder="POL001"
                    required
                    data-testid="policy-number-input"
                  />
                </div>

                <div>
                  <Label htmlFor="insurance_type">Insurance Type *</Label>
                  <Select value={newPolicy.insurance_type} onValueChange={(value) => setNewPolicy({ ...newPolicy, insurance_type: value })}>
                    <SelectTrigger data-testid="policy-type-select">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {insuranceTypes.map(type => (
                        <SelectItem key={type.value} value={type.value}>
                          {type.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="provider">Provider *</Label>
                  <Input
                    id="provider"
                    value={newPolicy.provider}
                    onChange={(e) => setNewPolicy({ ...newPolicy, provider: e.target.value })}
                    placeholder="Insurance Company Name"
                    required
                    data-testid="policy-provider-input"
                  />
                </div>

                <div>
                  <Label htmlFor="start_date">Start Date *</Label>
                  <Input
                    id="start_date"
                    type="date"
                    value={newPolicy.start_date}
                    onChange={(e) => setNewPolicy({ ...newPolicy, start_date: e.target.value })}
                    required
                    data-testid="policy-start-date-input"
                  />
                </div>

                <div>
                  <Label htmlFor="end_date">End Date *</Label>
                  <Input
                    id="end_date"
                    type="date"
                    value={newPolicy.end_date}
                    onChange={(e) => setNewPolicy({ ...newPolicy, end_date: e.target.value })}
                    required
                    data-testid="policy-end-date-input"
                  />
                </div>

                <div>
                  <Label htmlFor="sum_insured">Sum Insured ($) *</Label>
                  <Input
                    id="sum_insured"
                    type="number"
                    step="0.01"
                    value={newPolicy.sum_insured}
                    onChange={(e) => setNewPolicy({ ...newPolicy, sum_insured: e.target.value })}
                    placeholder="50000"
                    required
                    data-testid="policy-sum-insured-input"
                  />
                </div>

                <div>
                  <Label htmlFor="premium_amount">Premium Amount ($) *</Label>
                  <Input
                    id="premium_amount"
                    type="number"
                    step="0.01"
                    value={newPolicy.premium_amount}
                    onChange={(e) => setNewPolicy({ ...newPolicy, premium_amount: e.target.value })}
                    placeholder="1200"
                    required
                    data-testid="policy-premium-input"
                  />
                </div>

                {/* File upload removed; backend now expects JSON only */}
              </div>

              <div className="flex justify-end space-x-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setShowAddDialog(false)}
                  data-testid="cancel-policy-btn"
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={formLoading}
                  className="bg-gradient-to-r from-blue-600 to-indigo-600"
                  data-testid="save-policy-btn"
                >
                  {formLoading ? 'Creating...' : 'Create Policy'}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Filters */}
      <Card className="border-0 shadow-sm">
        <CardContent className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                placeholder="Search policies..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
                data-testid="policy-search-input"
              />
            </div>

            <Select value={filterStatus} onValueChange={setFilterStatus}>
              <SelectTrigger data-testid="policy-status-filter">
                <SelectValue placeholder="Filter by status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ALL">All Statuses</SelectItem>
                {policyStatuses.map(status => (
                  <SelectItem key={status.value} value={status.value}>
                    {status.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={filterType} onValueChange={setFilterType}>
              <SelectTrigger data-testid="policy-type-filter">
                <SelectValue placeholder="Filter by type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ALL">All Types</SelectItem>
                {insuranceTypes.map(type => (
                  <SelectItem key={type.value} value={type.value}>
                    {type.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* Policy Grid */}
      {filteredPolicies.length === 0 ? (
        <div className="text-center py-12">
          <FileText className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No policies found</h3>
          <p className="text-gray-500 mb-4">
            {searchTerm || filterStatus !== 'ALL' || filterType !== 'ALL'
              ? 'Try adjusting your filters'
              : 'Create your first insurance policy'
            }
          </p>
          <Button onClick={() => setShowAddDialog(true)} data-testid="create-first-policy-btn">
            <Plus className="w-4 h-4 mr-2" />
            Create Policy
          </Button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredPolicies.map((policy) => {
            const isExpiring = isExpiringSoon(policy.end_date);

            return (
              <Card key={policy.id} className={`hover:shadow-lg transition-all duration-200 border-0 bg-gradient-to-br from-white to-gray-50 ${isExpiring ? 'ring-2 ring-yellow-200' : ''
                }`}>
                <CardContent className="p-6">
                  <div className="space-y-4">
                    {/* Header */}
                    <div className="flex items-start justify-between">
                      <div className="space-y-1">
                        <h3 className="font-semibold text-gray-900" data-testid={`policy-number-${policy.id}`}>
                          {policy.policy_number}
                        </h3>
                        <p className="text-sm text-gray-600">{getEntityName(policy.entity_id)}</p>
                      </div>
                      <div className="flex flex-col items-end space-y-1">
                        {getStatusBadge(policy.status)}
                        {isExpiring && (
                          <Badge className="bg-yellow-100 text-yellow-800 border-0">
                            <AlertTriangle className="w-3 h-3 mr-1" />
                            Expiring Soon
                          </Badge>
                        )}
                      </div>
                    </div>

                    {/* Policy Details */}
                    <div className="space-y-3">
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-gray-600">Type:</span>
                        {getTypeBadge(policy.insurance_type)}
                      </div>

                      <div className="flex items-center justify-between">
                        <span className="text-sm text-gray-600">Provider:</span>
                        <span className="text-sm font-medium">{policy.provider}</span>
                      </div>

                      <div className="flex items-center justify-between">
                        <span className="text-sm text-gray-600">Premium:</span>
                        <span className="text-sm font-bold text-green-600">
                          {formatCurrency(policy.premium_amount)}
                        </span>
                      </div>

                      <div className="flex items-center justify-between">
                        <span className="text-sm text-gray-600">Sum Insured:</span>
                        <span className="text-sm font-medium">
                          {formatCurrency(policy.sum_insured)}
                        </span>
                      </div>

                      <div className="pt-2 border-t border-gray-100">
                        <div className="flex items-center justify-between text-xs text-gray-600">
                          <span>Valid from {formatDate(policy.start_date)}</span>
                          <span>to {formatDate(policy.end_date)}</span>
                        </div>
                      </div>
                    </div>

                                        {/* Actions */}
                    <div className="flex space-x-2 pt-2">
                      <Button 
                        variant="outline" 
                        size="sm" 
                        className="flex-1"
                        onClick={() => {
                          setViewPolicy(policy);
                          setShowViewDialog(true);
                        }}
                      >   
                        <Eye className="w-4 h-4 mr-1" />
                        View
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        className="flex-1"
                        onClick={() => {
                          setEditPolicy(policy);
                          setShowEditDialog(true);
                        }}
                      >
                        <Edit2 className="w-4 h-4 mr-1" />
                        Edit
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        className="flex-1 text-red-600"
                        onClick={() => handleDeletePolicy(policy.id)}
                      >
                        Delete
                      </Button>
                      {policy.status === 'ACTIVE' && (
                        <Button
                          variant="outline"
                          size="sm"
                          className="text-red-600 hover:text-red-700"
                          onClick={() => updatePolicyStatus(policy.id, 'CANCELLED')}
                        >
                          Cancel
                        </Button>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      )}

      {/* Edit Policy Dialog */}
      <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Edit Policy</DialogTitle>
          </DialogHeader>
          {editPolicy && (
            <form onSubmit={handleUpdatePolicy} className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="edit_entity_id">Entity *</Label>
                  <Select value={editPolicy.entity_id} onValueChange={(value) => setEditPolicy({ ...editPolicy, entity_id: value })}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select entity" />
                    </SelectTrigger>
                    <SelectContent>
                      {entities.map(entity => (
                        <SelectItem key={entity.id} value={entity.id}>
                          {entity.description}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="edit_policy_number">Policy Number *</Label>
                  <Input
                    id="edit_policy_number"
                    value={editPolicy.policy_number}
                    onChange={(e) => setEditPolicy({ ...editPolicy, policy_number: e.target.value })}
                    placeholder="POL001"
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_insurance_type">Insurance Type *</Label>
                  <Select value={editPolicy.insurance_type} onValueChange={(value) => setEditPolicy({ ...editPolicy, insurance_type: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {insuranceTypes.map(type => (
                        <SelectItem key={type.value} value={type.value}>
                          {type.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="edit_provider">Provider *</Label>
                  <Input
                    id="edit_provider"
                    value={editPolicy.provider}
                    onChange={(e) => setEditPolicy({ ...editPolicy, provider: e.target.value })}
                    placeholder="Insurance Company Name"
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_start_date">Start Date *</Label>
                  <Input
                    id="edit_start_date"
                    type="date"
                    value={editPolicy.start_date ? editPolicy.start_date.split('T')[0] : ''}
                    onChange={(e) => setEditPolicy({ ...editPolicy, start_date: e.target.value })}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_end_date">End Date *</Label>
                  <Input
                    id="edit_end_date"
                    type="date"
                    value={editPolicy.end_date ? editPolicy.end_date.split('T')[0] : ''}
                    onChange={(e) => setEditPolicy({ ...editPolicy, end_date: e.target.value })}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_sum_insured">Sum Insured ($) *</Label>
                  <Input
                    id="edit_sum_insured"
                    type="number"
                    step="0.01"
                    value={editPolicy.sum_insured}
                    onChange={(e) => setEditPolicy({ ...editPolicy, sum_insured: e.target.value })}
                    placeholder="50000"
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_premium_amount">Premium Amount ($) *</Label>
                  <Input
                    id="edit_premium_amount"
                    type="number"
                    step="0.01"
                    value={editPolicy.premium_amount}
                    onChange={(e) => setEditPolicy({ ...editPolicy, premium_amount: e.target.value })}
                    placeholder="1200"
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="edit_status">Status *</Label>
                  <Select value={editPolicy.status} onValueChange={(value) => setEditPolicy({ ...editPolicy, status: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {policyStatuses.map(status => (
                        <SelectItem key={status.value} value={status.value}>
                          {status.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="flex justify-end space-x-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setShowEditDialog(false)}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={formLoading}
                  className="bg-gradient-to-r from-blue-600 to-indigo-600"
                >
                  {formLoading ? 'Updating...' : 'Update Policy'}
                </Button>
              </div>
            </form>
          )}
        </DialogContent>
      </Dialog>

      {/* View Policy Dialog */}
      <Dialog open={showViewDialog} onOpenChange={setShowViewDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Policy Details</DialogTitle>
          </DialogHeader>
          {viewPolicy && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-gray-600">Policy Number</Label>
                  <p className="font-medium">{viewPolicy.policy_number}</p>
                </div>
                <div>
                  <Label className="text-gray-600">Status</Label>
                  <div className="mt-1">{getStatusBadge(viewPolicy.status)}</div>
                </div>
                <div>
                  <Label className="text-gray-600">Insurance Type</Label>
                  <div className="mt-1">{getTypeBadge(viewPolicy.insurance_type)}</div>
                </div>
                <div>
                  <Label className="text-gray-600">Provider</Label>
                  <p className="font-medium">{viewPolicy.provider}</p>
                </div>
                <div>
                  <Label className="text-gray-600">Entity</Label>
                  <p className="font-medium">{getEntityName(viewPolicy.entity_id)}</p>
                </div>
                <div>
                  <Label className="text-gray-600">Sum Insured</Label>
                  <p className="font-medium">{formatCurrency(viewPolicy.sum_insured)}</p>
                </div>
                <div>
                  <Label className="text-gray-600">Premium Amount</Label>
                  <p className="font-medium text-green-600">{formatCurrency(viewPolicy.premium_amount)}</p>
                </div>
                <div>
                  <Label className="text-gray-600">Start Date</Label>
                  <p className="font-medium">{formatDate(viewPolicy.start_date)}</p>
                </div>
                <div>
                  <Label className="text-gray-600">End Date</Label>
                  <p className="font-medium">{formatDate(viewPolicy.end_date)}</p>
                </div>
              </div>
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

export default PolicyManagement;