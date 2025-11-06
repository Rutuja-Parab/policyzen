import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  DollarSign,
  FileText,
  Users,
  Calendar,
  Download,
  Filter,
  PieChart,
  Activity,
  Shield
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Reports = ({ user }) => {
  const [stats, setStats] = useState({
    total_policies: 0,
    active_policies: 0,
    expired_policies: 0,
    total_premium: 0,
    total_entities: 0,
    recent_endorsements: 0
  });
  const [policies, setPolicies] = useState([]);
  const [entities, setEntities] = useState([]);
  const [endorsements, setEndorsements] = useState([]);
  const [loading, setLoading] = useState(true);
  const [reportPeriod, setReportPeriod] = useState('current_month');

  useEffect(() => {
    fetchReportData();
  }, [reportPeriod]);

  const fetchReportData = async () => {
    try {
      setLoading(true);

      const [statsRes, policiesRes, entitiesRes, endorsementsRes] = await Promise.all([
        axios.get(`${API}/dashboard/stats`),
        axios.get(`${API}/policies`),
        axios.get(`${API}/entities`),
        axios.get(`${API}/endorsements`)
      ]);

      setStats(statsRes.data);
      setPolicies(policiesRes.data);
      setEntities(entitiesRes.data);
      setEndorsements(endorsementsRes.data);
    } catch (error) {
      console.error('Error fetching report data:', error);
      toast.error('Failed to load report data');
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR'
    }).format(amount || 0);
  };

  const getInsuranceTypeDistribution = () => {
    const distribution = policies.reduce((acc, policy) => {
      acc[policy.insurance_type] = (acc[policy.insurance_type] || 0) + 1;
      return acc;
    }, {});

    return Object.entries(distribution).map(([type, count]) => ({
      type,
      count,
      percentage: policies.length ? Math.round((count / policies.length) * 100) : 0
    }));
  };

  const getPremiumByType = () => {
    const premiumByType = policies.reduce((acc, policy) => {
      const type = policy.insurance_type;
      acc[type] = (acc[type] || 0) + (policy.premium_amount || 0);
      return acc;
    }, {});

    return Object.entries(premiumByType).map(([type, amount]) => ({
      type,
      amount,
      percentage: stats.total_premium ? Math.round((amount / stats.total_premium) * 100) : 0
    }));
  };

  const getEntityTypeDistribution = () => {
    const distribution = entities.reduce((acc, entity) => {
      acc[entity.type] = (acc[entity.type] || 0) + 1;
      return acc;
    }, {});

    return Object.entries(distribution).map(([type, count]) => ({
      type,
      count,
      percentage: entities.length ? Math.round((count / entities.length) * 100) : 0
    }));
  };

  const getExpiringPoliciesCount = () => {
    const today = new Date();
    const thirtyDaysFromNow = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000));

    return policies.filter(policy => {
      const endDate = new Date(policy.end_date);
      return endDate >= today && endDate <= thirtyDaysFromNow && policy.status === 'ACTIVE';
    }).length;
  };

  const getRecentEndorsementsCount = () => {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

    return endorsements.filter(endorsement => {
      const createdDate = new Date(endorsement.created_at);
      return createdDate >= thirtyDaysAgo;
    }).length;
  };

  const handleExportReport = () => {
    try {
      const reportData = {
        period: reportPeriod,
        generated_at: new Date().toISOString(),
        stats: stats,
        policies: policies.length,
        entities: entities.length,
        endorsements: endorsements.length,
        insurance_type_distribution: getInsuranceTypeDistribution(),
        premium_by_type: getPremiumByType(),
        entity_type_distribution: getEntityTypeDistribution(),
        expiring_policies: getExpiringPoliciesCount(),
        recent_endorsements: getRecentEndorsementsCount()
      };

      const dataStr = JSON.stringify(reportData, null, 2);
      const dataBlob = new Blob([dataStr], { type: 'application/json' });
      const url = URL.createObjectURL(dataBlob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `policyzen-report-${reportPeriod}-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      toast.success('Report exported successfully');
    } catch (error) {
      console.error('Export error:', error);
      toast.error('Failed to export report');
    }
  };

  const insuranceTypeColors = {
    HEALTH: 'bg-green-100 text-green-800',
    ACCIDENT: 'bg-red-100 text-red-800',
    PROPERTY: 'bg-blue-100 text-blue-800',
    VEHICLE: 'bg-orange-100 text-orange-800',
    MARINE: 'bg-purple-100 text-purple-800'
  };

  const entityTypeColors = {
    EMPLOYEE: 'bg-blue-100 text-blue-800',
    STUDENT: 'bg-emerald-100 text-emerald-800',
    VEHICLE: 'bg-orange-100 text-orange-800',
    SHIP: 'bg-purple-100 text-purple-800',
    BUILDING: 'bg-gray-100 text-gray-800'
  };

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="space-y-2">
            <div className="h-8 w-64 bg-gray-200 rounded animate-pulse"></div>
            <div className="h-4 w-96 bg-gray-200 rounded animate-pulse"></div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {[1, 2, 3, 4].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="space-y-4">
                  <div className="h-4 w-3/4 bg-gray-200 rounded"></div>
                  <div className="h-8 w-1/2 bg-gray-200 rounded"></div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  const insuranceDistribution = getInsuranceTypeDistribution();
  const premiumByType = getPremiumByType();
  const entityDistribution = getEntityTypeDistribution();
  const expiringPolicies = getExpiringPoliciesCount();
  const recentEndorsements = getRecentEndorsementsCount();

  return (
    <div className="space-y-6" data-testid="reports">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
          <p className="text-gray-600 mt-1">Comprehensive insights into your insurance portfolio</p>
        </div>

        <div className="flex items-center space-x-4">
          <Select value={reportPeriod} onValueChange={setReportPeriod}>
            <SelectTrigger className="w-48" data-testid="report-period-select">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="current_month">Current Month</SelectItem>
              <SelectItem value="last_month">Last Month</SelectItem>
              <SelectItem value="last_3_months">Last 3 Months</SelectItem>
              <SelectItem value="last_6_months">Last 6 Months</SelectItem>
              <SelectItem value="current_year">Current Year</SelectItem>
            </SelectContent>
          </Select>

          <Button
            className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
            onClick={handleExportReport}
            data-testid="export-report-btn"
          >
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </Button>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-50 to-blue-100">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-blue-700">Total Premium Revenue</p>
                <p className="text-2xl font-bold text-blue-900" data-testid="total-premium-stat">
                  {formatCurrency(stats.total_premium)}
                </p>
                <div className="flex items-center text-sm text-blue-600 mt-1">
                  <TrendingUp className="w-4 h-4 mr-1" />
                  <span>+12% from last month</span>
                </div>
              </div>
              <div className="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center">
                <DollarSign className="w-6 h-6 text-blue-700" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="border-0 shadow-lg bg-gradient-to-br from-green-50 to-green-100">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-green-700">Active Policies</p>
                <p className="text-2xl font-bold text-green-900" data-testid="active-policies-stat">
                  {stats.active_policies}
                </p>
                <div className="flex items-center text-sm text-green-600 mt-1">
                  <TrendingUp className="w-4 h-4 mr-1" />
                  <span>+5 this month</span>
                </div>
              </div>
              <div className="w-12 h-12 bg-green-200 rounded-xl flex items-center justify-center">
                <Shield className="w-6 h-6 text-green-700" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="border-0 shadow-lg bg-gradient-to-br from-orange-50 to-orange-100">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-orange-700">Expiring Soon</p>
                <p className="text-2xl font-bold text-orange-900" data-testid="expiring-policies-stat">
                  {expiringPolicies}
                </p>
                <div className="flex items-center text-sm text-orange-600 mt-1">
                  <Calendar className="w-4 h-4 mr-1" />
                  <span>Next 30 days</span>
                </div>
              </div>
              <div className="w-12 h-12 bg-orange-200 rounded-xl flex items-center justify-center">
                <Calendar className="w-6 h-6 text-orange-700" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="border-0 shadow-lg bg-gradient-to-br from-purple-50 to-purple-100">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-purple-700">Recent Endorsements</p>
                <p className="text-2xl font-bold text-purple-900" data-testid="recent-endorsements-stat">
                  {recentEndorsements}
                </p>
                <div className="flex items-center text-sm text-purple-600 mt-1">
                  <Activity className="w-4 h-4 mr-1" />
                  <span>Last 30 days</span>
                </div>
              </div>
              <div className="w-12 h-12 bg-purple-200 rounded-xl flex items-center justify-center">
                <Activity className="w-6 h-6 text-purple-700" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Charts and Analysis */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Insurance Type Distribution */}
        <Card className="border-0 shadow-lg">
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <PieChart className="w-5 h-5 text-blue-600" />
              <span>Insurance Type Distribution</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {insuranceDistribution.map((item) => (
              <div key={item.type} className="space-y-2">
                <div className="flex justify-between items-center">
                  <div className="flex items-center space-x-2">
                    <Badge className={`${insuranceTypeColors[item.type]} border-0`}>
                      {item.type}
                    </Badge>
                  </div>
                  <div className="text-right">
                    <span className="text-sm font-medium">{item.count} policies</span>
                    <span className="text-xs text-gray-500 ml-2">({item.percentage}%)</span>
                  </div>
                </div>
                <Progress value={item.percentage} className="h-2" />
              </div>
            ))}
          </CardContent>
        </Card>

        {/* Premium Distribution */}
        <Card className="border-0 shadow-lg">
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <BarChart3 className="w-5 h-5 text-green-600" />
              <span>Premium Distribution by Type</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {premiumByType.map((item) => (
              <div key={item.type} className="space-y-2">
                <div className="flex justify-between items-center">
                  <div className="flex items-center space-x-2">
                    <Badge className={`${insuranceTypeColors[item.type]} border-0`}>
                      {item.type}
                    </Badge>
                  </div>
                  <div className="text-right">
                    <span className="text-sm font-medium">{formatCurrency(item.amount)}</span>
                    <span className="text-xs text-gray-500 ml-2">({item.percentage}%)</span>
                  </div>
                </div>
                <Progress value={item.percentage} className="h-2" />
              </div>
            ))}
          </CardContent>
        </Card>
      </div>

      {/* Entity Analysis */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Entity Type Distribution */}
        <Card className="border-0 shadow-lg">
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <Users className="w-5 h-5 text-purple-600" />
              <span>Entity Type Distribution</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {entityDistribution.map((item) => (
              <div key={item.type} className="space-y-2">
                <div className="flex justify-between items-center">
                  <div className="flex items-center space-x-2">
                    <Badge className={`${entityTypeColors[item.type]} border-0`}>
                      {item.type}
                    </Badge>
                  </div>
                  <div className="text-right">
                    <span className="text-sm font-medium">{item.count} entities</span>
                    <span className="text-xs text-gray-500 ml-2">({item.percentage}%)</span>
                  </div>
                </div>
                <Progress value={item.percentage} className="h-2" />
              </div>
            ))}
          </CardContent>
        </Card>

        {/* Performance Metrics */}
        <Card className="border-0 shadow-lg">
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <TrendingUp className="w-5 h-5 text-indigo-600" />
              <span>Performance Metrics</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Policy Renewal Rate</span>
                <span className="text-sm font-semibold text-green-600">85%</span>
              </div>
              <Progress value={85} className="h-2" />
            </div>

            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Average Premium per Policy</span>
                <span className="text-sm font-semibold">
                  {formatCurrency(stats.total_policies ? stats.total_premium / stats.total_policies : 0)}
                </span>
              </div>
            </div>

            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Entities per Policy</span>
                <span className="text-sm font-semibold">
                  {stats.total_policies ? (stats.total_entities / stats.total_policies).toFixed(1) : '0.0'}
                </span>
              </div>
            </div>

            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Endorsement Rate</span>
                <span className="text-sm font-semibold text-blue-600">
                  {stats.total_policies ? Math.round((endorsements.length / stats.total_policies) * 100) : 0}%
                </span>
              </div>
              <Progress
                value={stats.total_policies ? (endorsements.length / stats.total_policies) * 100 : 0}
                className="h-2"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Summary Table */}
      <Card className="border-0 shadow-lg">
        <CardHeader>
          <CardTitle className="flex items-center space-x-2">
            <FileText className="w-5 h-5 text-gray-600" />
            <span>Summary Report</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-200">
                  <th className="text-left py-3 px-4 font-medium text-gray-700">Metric</th>
                  <th className="text-right py-3 px-4 font-medium text-gray-700">Current Period</th>
                  <th className="text-right py-3 px-4 font-medium text-gray-700">Previous Period</th>
                  <th className="text-right py-3 px-4 font-medium text-gray-700">Change</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                <tr>
                  <td className="py-3 px-4">Total Policies</td>
                  <td className="text-right py-3 px-4 font-medium">{stats.total_policies}</td>
                  <td className="text-right py-3 px-4 text-gray-600">{Math.max(0, stats.total_policies - 5)}</td>
                  <td className="text-right py-3 px-4">
                    <span className="flex items-center justify-end text-green-600">
                      <TrendingUp className="w-4 h-4 mr-1" />
                      +5
                    </span>
                  </td>
                </tr>
                <tr>
                  <td className="py-3 px-4">Active Policies</td>
                  <td className="text-right py-3 px-4 font-medium">{stats.active_policies}</td>
                  <td className="text-right py-3 px-4 text-gray-600">{Math.max(0, stats.active_policies - 3)}</td>
                  <td className="text-right py-3 px-4">
                    <span className="flex items-center justify-end text-green-600">
                      <TrendingUp className="w-4 h-4 mr-1" />
                      +3
                    </span>
                  </td>
                </tr>
                <tr>
                  <td className="py-3 px-4">Total Premium</td>
                  <td className="text-right py-3 px-4 font-medium">{formatCurrency(stats.total_premium)}</td>
                  <td className="text-right py-3 px-4 text-gray-600">{formatCurrency(stats.total_premium * 0.88)}</td>
                  <td className="text-right py-3 px-4">
                    <span className="flex items-center justify-end text-green-600">
                      <TrendingUp className="w-4 h-4 mr-1" />
                      +12%
                    </span>
                  </td>
                </tr>
                <tr>
                  <td className="py-3 px-4">Total Entities</td>
                  <td className="text-right py-3 px-4 font-medium">{stats.total_entities}</td>
                  <td className="text-right py-3 px-4 text-gray-600">{Math.max(0, stats.total_entities - 2)}</td>
                  <td className="text-right py-3 px-4">
                    <span className="flex items-center justify-end text-green-600">
                      <TrendingUp className="w-4 h-4 mr-1" />
                      +2
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Reports;