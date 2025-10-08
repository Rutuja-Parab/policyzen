import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Button } from '@/components/ui/button';
import { 
  FileText, 
  Users, 
  TrendingUp, 
  AlertCircle,
  Shield,
  DollarSign,
  Calendar,
  Activity,
  ArrowUpRight,
  ArrowDownRight,
  Clock,
  CheckCircle,
  XCircle
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Dashboard = ({ user }) => {
  const [stats, setStats] = useState({
    total_policies: 0,
    active_policies: 0,
    expired_policies: 0,
    total_premium: 0,
    total_entities: 0,
    recent_endorsements: 0
  });
  const [loading, setLoading] = useState(true);
  const [recentPolicies, setRecentPolicies] = useState([]);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      
      // Fetch dashboard stats
      const statsResponse = await axios.get(`${API}/dashboard/stats`);
      setStats(statsResponse.data);
      
      // Fetch recent policies
      const policiesResponse = await axios.get(`${API}/policies`);
      setRecentPolicies(policiesResponse.data.slice(0, 5)); // Get first 5 policies
      
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      toast.error('Failed to load dashboard data');
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const getStatusBadge = (status) => {
    const statusConfig = {
      'ACTIVE': { color: 'bg-green-100 text-green-800', icon: CheckCircle },
      'EXPIRED': { color: 'bg-red-100 text-red-800', icon: XCircle },
      'UNDER_REVIEW': { color: 'bg-yellow-100 text-yellow-800', icon: Clock },
      'CANCELLED': { color: 'bg-gray-100 text-gray-800', icon: XCircle }
    };
    
    const config = statusConfig[status] || statusConfig['ACTIVE'];
    const Icon = config.icon;
    
    return (
      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.color}`}>
        <Icon className="w-3 h-3 mr-1" />
        {status}
      </span>
    );
  };

  const StatCard = ({ title, value, icon: Icon, color, subValue, subLabel, trend, loading }) => (
    <Card className="relative overflow-hidden group hover:shadow-lg transition-all duration-300 border-0 bg-gradient-to-br from-white to-gray-50">
      <CardContent className="p-6">
        <div className="flex items-center justify-between">
          <div className="space-y-2">
            <p className="text-sm font-medium text-gray-600">{title}</p>
            <div className="space-y-1">
              {loading ? (
                <div className="h-8 w-24 bg-gray-200 rounded animate-pulse"></div>
              ) : (
                <p className="text-3xl font-bold text-gray-900" data-testid={`stat-${title.toLowerCase().replace(/\s+/g, '-')}`}>
                  {typeof value === 'number' && title.includes('Premium') ? formatCurrency(value) : value}
                </p>
              )}
              {subValue && (
                <div className="flex items-center space-x-1 text-sm">
                  {trend === 'up' ? (
                    <ArrowUpRight className="w-4 h-4 text-green-500" />
                  ) : trend === 'down' ? (
                    <ArrowDownRight className="w-4 h-4 text-red-500" />
                  ) : null}
                  <span className={`font-medium ${
                    trend === 'up' ? 'text-green-600' : 
                    trend === 'down' ? 'text-red-600' : 'text-gray-600'
                  }`}>
                    {subValue} {subLabel}
                  </span>
                </div>
              )}
            </div>
          </div>
          <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${color} group-hover:scale-110 transition-transform duration-200`}>
            <Icon className="w-6 h-6 text-white" />
          </div>
        </div>
        
        {/* Animated background gradient */}
        <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
      </CardContent>
    </Card>
  );

  if (loading) {
    return (
      <div className="space-y-8 animate-fade-in">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div className="space-y-2">
                    <div className="h-4 w-24 bg-gray-200 rounded"></div>
                    <div className="h-8 w-32 bg-gray-200 rounded"></div>
                  </div>
                  <div className="w-12 h-12 bg-gray-200 rounded-xl"></div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-fade-in" data-testid="dashboard">
      {/* Welcome Header */}
      <div className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
        <div className="relative z-10">
          <h1 className="text-3xl font-bold mb-2">Welcome back, {user?.name || 'User'}!</h1>
          <p className="text-blue-100 text-lg">Here's your insurance portfolio overview</p>
        </div>
        <div className="absolute top-0 right-0 w-64 h-64 opacity-10">
          <Shield className="w-full h-full" />
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <StatCard
          title="Total Policies"
          value={stats.total_policies}
          icon={FileText}
          color="bg-gradient-to-r from-blue-500 to-blue-600"
          subValue={stats.active_policies}
          subLabel="active"
          trend="up"
          loading={loading}
        />
        
        <StatCard
          title="Total Entities"
          value={stats.total_entities}
          icon={Users}
          color="bg-gradient-to-r from-emerald-500 to-emerald-600"
          loading={loading}
        />
        
        <StatCard
          title="Total Premium"
          value={stats.total_premium}
          icon={DollarSign}
          color="bg-gradient-to-r from-purple-500 to-purple-600"
          loading={loading}
        />
        
        <StatCard
          title="Active Policies"
          value={stats.active_policies}
          icon={CheckCircle}
          color="bg-gradient-to-r from-green-500 to-green-600"
          loading={loading}
        />
        
        <StatCard
          title="Expired Policies"
          value={stats.expired_policies}
          icon={AlertCircle}
          color="bg-gradient-to-r from-red-500 to-red-600"
          loading={loading}
        />
        
        <StatCard
          title="Recent Endorsements"
          value={stats.recent_endorsements}
          icon={Activity}
          color="bg-gradient-to-r from-orange-500 to-orange-600"
          subLabel="this month"
          loading={loading}
        />
      </div>

      {/* Policy Overview and Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Policy Status Distribution */}
        <Card className="border-0 shadow-lg">
          <CardHeader className="pb-4">
            <CardTitle className="flex items-center space-x-2">
              <TrendingUp className="w-5 h-5 text-blue-600" />
              <span>Policy Status Distribution</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-4">
              <div>
                <div className="flex justify-between text-sm mb-2">
                  <span className="font-medium text-gray-700">Active Policies</span>
                  <span className="text-green-600 font-semibold">{stats.active_policies}</span>
                </div>
                <Progress 
                  value={stats.total_policies ? (stats.active_policies / stats.total_policies) * 100 : 0} 
                  className="h-2"
                />
              </div>
              
              <div>
                <div className="flex justify-between text-sm mb-2">
                  <span className="font-medium text-gray-700">Expired Policies</span>
                  <span className="text-red-600 font-semibold">{stats.expired_policies}</span>
                </div>
                <Progress 
                  value={stats.total_policies ? (stats.expired_policies / stats.total_policies) * 100 : 0} 
                  className="h-2"
                />
              </div>
            </div>
            
            <div className="pt-4 border-t">
              <div className="text-center">
                <p className="text-2xl font-bold text-gray-900">
                  {stats.total_policies ? Math.round((stats.active_policies / stats.total_policies) * 100) : 0}%
                </p>
                <p className="text-sm text-gray-600">Policy Success Rate</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Recent Policies */}
        <Card className="border-0 shadow-lg">
          <CardHeader className="pb-4">
            <CardTitle className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <Calendar className="w-5 h-5 text-blue-600" />
                <span>Recent Policies</span>
              </div>
              <Button variant="ghost" size="sm" className="text-blue-600 hover:text-blue-700">
                View All
              </Button>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentPolicies.length > 0 ? (
                recentPolicies.map((policy) => (
                  <div 
                    key={policy.id} 
                    className="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200"
                  >
                    <div>
                      <p className="font-medium text-gray-900 text-sm">
                        {policy.policy_number}
                      </p>
                      <p className="text-xs text-gray-600">
                        {policy.insurance_type} • {formatCurrency(policy.premium_amount)}
                      </p>
                    </div>
                    {getStatusBadge(policy.status)}
                  </div>
                ))
              ) : (
                <div className="text-center py-8">
                  <FileText className="w-12 h-12 text-gray-300 mx-auto mb-2" />
                  <p className="text-gray-500">No policies found</p>
                  <Button variant="ghost" size="sm" className="mt-2 text-blue-600">
                    Create First Policy
                  </Button>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card className="border-0 shadow-lg">
        <CardHeader>
          <CardTitle className="flex items-center space-x-2">
            <Activity className="w-5 h-5 text-blue-600" />
            <span>Quick Actions</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Button 
              className="h-16 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white"
              data-testid="quick-action-new-policy"
            >
              <FileText className="w-5 h-5 mr-2" />
              New Policy
            </Button>
            
            <Button 
              variant="outline" 
              className="h-16 border-emerald-200 text-emerald-600 hover:bg-emerald-50"
              data-testid="quick-action-add-entity"
            >
              <Users className="w-5 h-5 mr-2" />
              Add Entity
            </Button>
            
            <Button 
              variant="outline" 
              className="h-16 border-purple-200 text-purple-600 hover:bg-purple-50"
              data-testid="quick-action-create-endorsement"
            >
              <Activity className="w-5 h-5 mr-2" />
              Create Endorsement
            </Button>
            
            <Button 
              variant="outline" 
              className="h-16 border-orange-200 text-orange-600 hover:bg-orange-50"
              data-testid="quick-action-view-reports"
            >
              <TrendingUp className="w-5 h-5 mr-2" />
              View Reports
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;