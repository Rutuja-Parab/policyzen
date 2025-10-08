import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Shield, Mail, Lock, Users, FileText, TrendingUp } from 'lucide-react';
import axios from 'axios';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Login = ({ onLogin }) => {
  const [isLogin, setIsLogin] = useState(true);
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    name: '',
    company_id: '',
    role: 'CLIENT'
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      if (isLogin) {
        // Login
        const response = await axios.post(`${API}/auth/login`, {
          email: formData.email,
          password: formData.password
        }, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          transformRequest: [(data) => {
            const params = new URLSearchParams();
            params.append('email', data.email);
            params.append('password', data.password);
            return params;
          }]
        });
        
        if (response.data.user) {
          onLogin(response.data.user);
        }
      } else {
        // Register
        const registerData = {
          name: formData.name,
          email: formData.email,
          password: formData.password,
          company_id: formData.company_id || 'default-company',
          role: formData.role
        };
        
        const response = await axios.post(`${API}/auth/register`, registerData);
        
        if (response.data) {
          toast.success('Registration successful! Please log in.');
          setIsLogin(true);
          setFormData({ ...formData, password: '', name: '', company_id: '' });
        }
      }
    } catch (error) {
      console.error('Auth error:', error);
      const errorMessage = error.response?.data?.detail || 'Authentication failed';
      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 flex items-center justify-center p-4">
      {/* Background Pattern */}
      <div className="absolute inset-0 opacity-5">
        <div className="absolute inset-0" style={{
          backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.1'%3E%3Ccircle cx='7' cy='7' r='5'/%3E%3Ccircle cx='53' cy='53' r='5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`
        }}></div>
      </div>

      <div className="w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-center relative z-10">
        {/* Left Side - Branding */}
        <div className="hidden lg:block space-y-8">
          <div className="space-y-4">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center">
                <Shield className="w-7 h-7 text-white" />
              </div>
              <h1 className="text-4xl font-bold text-gray-900">PolicyZen</h1>
            </div>
            <p className="text-xl text-gray-600 leading-relaxed">
              Comprehensive Insurance Policy Management System for modern businesses
            </p>
          </div>

          {/* Feature Cards */}
          <div className="grid gap-4">
            <div className="flex items-center space-x-4 p-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20">
              <div className="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <Users className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <h3 className="font-semibold text-gray-900">Multi-Entity Management</h3>
                <p className="text-sm text-gray-600">Handle employees, students, vehicles, and vessels</p>
              </div>
            </div>
            
            <div className="flex items-center space-x-4 p-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20">
              <div className="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                <FileText className="w-5 h-5 text-emerald-600" />
              </div>
              <div>
                <h3 className="font-semibold text-gray-900">Smart Endorsements</h3>
                <p className="text-sm text-gray-600">Automated policy updates and modifications</p>
              </div>
            </div>
            
            <div className="flex items-center space-x-4 p-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20">
              <div className="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                <TrendingUp className="w-5 h-5 text-purple-600" />
              </div>
              <div>
                <h3 className="font-semibold text-gray-900">Advanced Analytics</h3>
                <p className="text-sm text-gray-600">Real-time reports and insights</p>
              </div>
            </div>
          </div>
        </div>

        {/* Right Side - Auth Form */}
        <div className="w-full max-w-md mx-auto">
          <Card className="shadow-2xl border-0 bg-white/80 backdrop-blur-xl">
            <CardHeader className="text-center space-y-4 pb-8">
              <div className="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto lg:hidden">
                <Shield className="w-8 h-8 text-white" />
              </div>
              <div>
                <CardTitle className="text-2xl font-bold">
                  {isLogin ? 'Welcome Back' : 'Get Started'}
                </CardTitle>
                <CardDescription className="text-base">
                  {isLogin 
                    ? 'Sign in to your PolicyZen account'
                    : 'Create your PolicyZen account'
                  }
                </CardDescription>
              </div>
            </CardHeader>
            
            <CardContent className="space-y-6">
              {error && (
                <Alert className="border-red-200 bg-red-50">
                  <AlertDescription className="text-red-800">{error}</AlertDescription>
                </Alert>
              )}
              
              <form onSubmit={handleSubmit} className="space-y-4">
                {!isLogin && (
                  <div className="space-y-2">
                    <Label htmlFor="name" className="text-sm font-medium">Full Name</Label>
                    <Input
                      id="name"
                      name="name"
                      type="text"
                      required={!isLogin}
                      value={formData.name}
                      onChange={handleInputChange}
                      className="h-11 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20"
                      placeholder="Enter your full name"
                      data-testid="register-name-input"
                    />
                  </div>
                )}
                
                <div className="space-y-2">
                  <Label htmlFor="email" className="text-sm font-medium">Email Address</Label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input
                      id="email"
                      name="email"
                      type="email"
                      required
                      value={formData.email}
                      onChange={handleInputChange}
                      className="h-11 pl-10 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20"
                      placeholder="Enter your email"
                      data-testid="email-input"
                    />
                  </div>
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="password" className="text-sm font-medium">Password</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input
                      id="password"
                      name="password"
                      type="password"
                      required
                      value={formData.password}
                      onChange={handleInputChange}
                      className="h-11 pl-10 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20"
                      placeholder="Enter your password"
                      data-testid="password-input"
                    />
                  </div>
                </div>
                
                {!isLogin && (
                  <div className="space-y-2">
                    <Label htmlFor="role" className="text-sm font-medium">Role</Label>
                    <select
                      id="role"
                      name="role"
                      value={formData.role}
                      onChange={handleInputChange}
                      className="w-full h-11 px-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all duration-200"
                      data-testid="register-role-select"
                    >
                      <option value="CLIENT">Client</option>
                      <option value="AGENT">Agent</option>
                      <option value="ADMIN">Admin</option>
                    </select>
                  </div>
                )}
                
                <Button 
                  type="submit" 
                  disabled={loading}
                  className="w-full h-11 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg"
                  data-testid={isLogin ? "login-submit-btn" : "register-submit-btn"}
                >
                  {loading ? (
                    <div className="flex items-center space-x-2">
                      <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                      <span>{isLogin ? 'Signing in...' : 'Creating account...'}</span>
                    </div>
                  ) : (
                    isLogin ? 'Sign In' : 'Create Account'
                  )}
                </Button>
              </form>
              
              <div className="text-center">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => {
                    setIsLogin(!isLogin);
                    setError('');
                    setFormData({
                      email: formData.email,
                      password: '',
                      name: '',
                      company_id: '',
                      role: 'CLIENT'
                    });
                  }}
                  className="text-blue-600 hover:text-blue-700 transition-colors duration-200"
                  data-testid="toggle-auth-mode-btn"
                >
                  {isLogin 
                    ? "Don't have an account? Sign up"
                    : "Already have an account? Sign in"
                  }
                </Button>
              </div>
            </CardContent>
          </Card>
          
          {/* Demo Credentials */}
          <div className="mt-6 p-4 bg-white/60 backdrop-blur-sm rounded-xl border border-white/20">
            <h3 className="font-medium text-gray-900 mb-2">Demo Credentials</h3>
            <div className="text-sm space-y-1">
              <p className="text-gray-600">Email: <span className="font-mono bg-gray-100 px-1 rounded">admin@policyzen.com</span></p>
              <p className="text-gray-600">Password: <span className="font-mono bg-gray-100 px-1 rounded">admin123</span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;