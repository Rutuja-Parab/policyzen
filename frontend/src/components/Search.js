import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  Search as SearchIcon, 
  FileText, 
  Users, 
  Edit,
  Filter,
  Clock,
  Eye,
  AlertCircle,
  CheckCircle,
  XCircle
} from 'lucide-react';
import { toast } from 'sonner';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Search = ({ user }) => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState({
    policies: [],
    entities: [],
    endorsements: []
  });
  const [loading, setLoading] = useState(false);
  const [searchType, setSearchType] = useState('all');
  const [entityTypeFilter, setEntityTypeFilter] = useState('');
  const [hasSearched, setHasSearched] = useState(false);

  const handleSearch = async (query = searchQuery) => {
    if (!query.trim()) {
      toast.error('Please enter a search term');
      return;
    }

    setLoading(true);
    setHasSearched(true);
    
    try {
      const params = {
        q: query.trim()
      };
      
      if (entityTypeFilter) {
        params.entity_type = entityTypeFilter;
      }
      
      const response = await axios.get(`${API}/search`, { params });
      setSearchResults(response.data);
    } catch (error) {
      console.error('Search error:', error);
      toast.error('Search failed. Please try again.');
      setSearchResults({ policies: [], entities: [], endorsements: [] });
    } finally {
      setLoading(false);
    }
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  };

  const clearSearch = () => {
    setSearchQuery('');
    setSearchResults({ policies: [], entities: [], endorsements: [] });
    setHasSearched(false);
    setEntityTypeFilter('');
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

  const getStatusBadge = (status) => {
    const statusConfig = {
      'ACTIVE': { color: 'bg-green-100 text-green-800', icon: CheckCircle },
      'EXPIRED': { color: 'bg-red-100 text-red-800', icon: XCircle },
      'UNDER_REVIEW': { color: 'bg-yellow-100 text-yellow-800', icon: Clock },
      'CANCELLED': { color: 'bg-gray-100 text-gray-800', icon: XCircle },
      'INACTIVE': { color: 'bg-gray-100 text-gray-800', icon: XCircle }
    };
    
    const config = statusConfig[status] || statusConfig['ACTIVE'];
    const Icon = config.icon;
    
    return (
      <Badge className={`${config.color} border-0`}>
        <Icon className="w-3 h-3 mr-1" />
        {status}
      </Badge>
    );
  };

  const getTotalResults = () => {
    return searchResults.policies.length + 
           searchResults.entities.length + 
           searchResults.endorsements.length;
  };

  const renderPolicyResults = () => {
    if (searchResults.policies.length === 0) return null;

    return (
      <Card className="border-0 shadow-sm">
        <CardHeader className="pb-4">
          <CardTitle className="flex items-center space-x-2">
            <FileText className="w-5 h-5 text-blue-600" />
            <span>Policies ({searchResults.policies.length})</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {searchResults.policies.map((policy) => (
              <div key={policy.id} className="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <div className="flex items-start justify-between mb-2">
                  <div>
                    <h4 className="font-semibold text-gray-900" data-testid={`search-policy-${policy.id}`}>
                      {policy.policy_number}
                    </h4>
                    <p className="text-sm text-gray-600">{policy.provider}</p>
                  </div>
                  {getStatusBadge(policy.status)}
                </div>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <span className="text-gray-500">Type:</span>
                    <p className="font-medium">{policy.insurance_type}</p>
                  </div>
                  <div>
                    <span className="text-gray-500">Premium:</span>
                    <p className="font-medium text-green-600">{formatCurrency(policy.premium_amount)}</p>
                  </div>
                  <div>
                    <span className="text-gray-500">Start Date:</span>
                    <p className="font-medium">{formatDate(policy.start_date)}</p>
                  </div>
                  <div>
                    <span className="text-gray-500">End Date:</span>
                    <p className="font-medium">{formatDate(policy.end_date)}</p>
                  </div>
                </div>
                <div className="flex space-x-2 mt-3">
                  <Button 
                    variant="outline" 
                    size="sm"
                    onClick={() => navigate('/policies')}
                  >
                    <Eye className="w-4 h-4 mr-1" />
                    View Details
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    );
  };

  const renderEntityResults = () => {
    if (searchResults.entities.length === 0) return null;

    return (
      <Card className="border-0 shadow-sm">
        <CardHeader className="pb-4">
          <CardTitle className="flex items-center space-x-2">
            <Users className="w-5 h-5 text-emerald-600" />
            <span>Entities ({searchResults.entities.length})</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {searchResults.entities.map((entity) => {
              // Determine entity details based on type
              const getEntityDetails = () => {
                if (entity.name) {
                  return {
                    title: entity.name,
                    subtitle: entity.employee_code || entity.student_id || entity.registration_number || entity.imo_number || 'N/A',
                    type: entity.employee_code ? 'Employee' : 
                          entity.student_id ? 'Student' :
                          entity.registration_number ? 'Vehicle' :
                          entity.imo_number ? 'Vessel' : 'Entity'
                  };
                }
                return {
                  title: 'Entity',
                  subtitle: 'N/A',
                  type: 'Unknown'
                };
              };
              
              const details = getEntityDetails();
              
              return (
                <div key={entity.id} className="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                  <div className="flex items-start justify-between mb-2">
                    <div>
                      <h4 className="font-semibold text-gray-900" data-testid={`search-entity-${entity.id}`}>
                        {details.title}
                      </h4>
                      <p className="text-sm text-gray-600">{details.subtitle}</p>
                    </div>
                    <Badge className="bg-emerald-100 text-emerald-800 border-0">
                      {details.type}
                    </Badge>
                  </div>
                  
                  {entity.status && (
                    <div className="mb-2">
                      {getStatusBadge(entity.status)}
                    </div>
                  )}
                  
                  <div className="flex space-x-2 mt-3">
                    <Button 
                      variant="outline" 
                      size="sm"
                      onClick={() => navigate('/entities')}
                    >
                      <Eye className="w-4 h-4 mr-1" />
                      View Details
                    </Button>
                  </div>
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    );
  };

  const renderEndorsementResults = () => {
    if (searchResults.endorsements.length === 0) return null;

    return (
      <Card className="border-0 shadow-sm">
        <CardHeader className="pb-4">
          <CardTitle className="flex items-center space-x-2">
            <Edit className="w-5 h-5 text-purple-600" />
            <span>Endorsements ({searchResults.endorsements.length})</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {searchResults.endorsements.map((endorsement) => (
              <div key={endorsement.id} className="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <div className="flex items-start justify-between mb-2">
                  <div>
                    <h4 className="font-semibold text-gray-900" data-testid={`search-endorsement-${endorsement.id}`}>
                      {endorsement.endorsement_number}
                    </h4>
                    <p className="text-sm text-gray-600 line-clamp-2">{endorsement.description}</p>
                  </div>
                  <Badge className="bg-purple-100 text-purple-800 border-0">
                    Endorsement
                  </Badge>
                </div>
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-gray-500">Effective Date:</span>
                    <p className="font-medium">{formatDate(endorsement.effective_date)}</p>
                  </div>
                  <div>
                    <span className="text-gray-500">Created:</span>
                    <p className="font-medium">{formatDate(endorsement.created_at)}</p>
                  </div>
                </div>
                <div className="flex space-x-2 mt-3">
                  <Button 
                    variant="outline" 
                    size="sm"
                    onClick={() => navigate('/endorsements')}
                  >
                    <Eye className="w-4 h-4 mr-1" />
                    View Details
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    );
  };

  return (
    <div className="space-y-6" data-testid="search">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Search</h1>
        <p className="text-gray-600 mt-1">Find policies, entities, and endorsements across your system</p>
      </div>

      {/* Search Interface */}
      <Card className="border-0 shadow-lg">
        <CardContent className="p-6">
          <div className="space-y-4">
            {/* Main Search Bar */}
            <div className="relative">
              <SearchIcon className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <Input
                type="text"
                placeholder="Search policies, entities, endorsements..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onKeyPress={handleKeyPress}
                className="pl-12 h-12 text-lg"
                data-testid="search-input"
              />
            </div>
            
            {/* Filters */}
            <div className="flex items-center space-x-4">
              <Select value={searchType} onValueChange={setSearchType}>
                <SelectTrigger className="w-48" data-testid="search-type-select">
                  <SelectValue placeholder="Search type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="policies">Policies Only</SelectItem>
                  <SelectItem value="entities">Entities Only</SelectItem>
                  <SelectItem value="endorsements">Endorsements Only</SelectItem>
                </SelectContent>
              </Select>
              
              <Select value={entityTypeFilter} onValueChange={setEntityTypeFilter}>
                <SelectTrigger className="w-48" data-testid="entity-type-filter">
                  <SelectValue placeholder="Entity type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Entities</SelectItem>
                  <SelectItem value="employee">Employees</SelectItem>
                  <SelectItem value="student">Students</SelectItem>
                  <SelectItem value="vessel">Vessels</SelectItem>
                  <SelectItem value="vehicle">Vehicles</SelectItem>
                </SelectContent>
              </Select>
              
              <Button 
                onClick={() => handleSearch()}
                disabled={loading || !searchQuery.trim()}
                className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
                data-testid="search-btn"
              >
                {loading ? (
                  <div className="flex items-center space-x-2">
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Searching...</span>
                  </div>
                ) : (
                  <>
                    <SearchIcon className="w-4 h-4 mr-2" />
                    Search
                  </>
                )}
              </Button>
              
              {hasSearched && (
                <Button variant="outline" onClick={clearSearch} data-testid="clear-search-btn">
                  Clear
                </Button>
              )}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Search Results */}
      {loading ? (
        <div className="space-y-6">
          {[1, 2, 3].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="space-y-4">
                  <div className="h-4 w-1/4 bg-gray-200 rounded"></div>
                  <div className="space-y-2">
                    <div className="h-4 w-3/4 bg-gray-200 rounded"></div>
                    <div className="h-4 w-1/2 bg-gray-200 rounded"></div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      ) : hasSearched ? (
        <div className="space-y-6">
          {/* Results Summary */}
          <Card className="border-0 shadow-sm bg-blue-50">
            <CardContent className="p-4">
              <div className="flex items-center space-x-2">
                <AlertCircle className="w-5 h-5 text-blue-600" />
                <span className="text-blue-900 font-medium">
                  Found {getTotalResults()} result{getTotalResults() !== 1 ? 's' : ''} for "{searchQuery}"
                </span>
              </div>
            </CardContent>
          </Card>
          
          {getTotalResults() === 0 ? (
            <Card className="border-0 shadow-sm">
              <CardContent className="p-12 text-center">
                <SearchIcon className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                <p className="text-gray-500">Try adjusting your search terms or filters</p>
              </CardContent>
            </Card>
          ) : (
            <div className="space-y-6">
              {renderPolicyResults()}
              {renderEntityResults()}
              {renderEndorsementResults()}
            </div>
          )}
        </div>
      ) : (
        /* Empty State */
        <Card className="border-0 shadow-sm">
          <CardContent className="p-12 text-center">
            <SearchIcon className="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">Start your search</h3>
            <p className="text-gray-500 mb-6">Enter keywords to find policies, entities, or endorsements</p>
            
            {/* Quick Search Suggestions */}
            <div className="space-y-3">
              <p className="text-sm font-medium text-gray-700">Quick suggestions:</p>
              <div className="flex flex-wrap justify-center gap-2">
                {['Health Insurance', 'Active Policies', 'Employees', 'Expired', 'Endorsements'].map((suggestion) => (
                  <Button 
                    key={suggestion}
                    variant="outline" 
                    size="sm"
                    onClick={() => {
                      setSearchQuery(suggestion);
                      handleSearch(suggestion);
                    }}
                    className="text-xs"
                  >
                    {suggestion}
                  </Button>
                ))}
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default Search;