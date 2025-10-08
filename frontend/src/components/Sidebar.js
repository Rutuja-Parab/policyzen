import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { 
  LayoutDashboard, 
  Users, 
  FileText, 
  Edit, 
  BarChart3, 
  Search, 
  Shield,
  LogOut,
  Menu,
  X,
  ChevronRight
} from 'lucide-react';
import { toast } from 'sonner';

const Sidebar = ({ isOpen, onToggle, user, onLogout }) => {
  const location = useLocation();

  const menuItems = [
    {
      name: 'Dashboard',
      path: '/dashboard',
      icon: LayoutDashboard,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      name: 'Entity Management',
      path: '/entities',
      icon: Users,
      color: 'text-emerald-600',
      bgColor: 'bg-emerald-50'
    },
    {
      name: 'Policy Management',
      path: '/policies',
      icon: FileText,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    },
    {
      name: 'Endorsements',
      path: '/endorsements',
      icon: Edit,
      color: 'text-orange-600',
      bgColor: 'bg-orange-50'
    },
    {
      name: 'Reports & Analytics',
      path: '/reports',
      icon: BarChart3,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-50'
    },
    {
      name: 'Search',
      path: '/search',
      icon: Search,
      color: 'text-gray-600',
      bgColor: 'bg-gray-50'
    }
  ];

  const isActive = (path) => location.pathname === path;

  const handleLogout = () => {
    onLogout();
  };

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-black/50 backdrop-blur-sm lg:hidden z-40"
          onClick={onToggle}
        />
      )}

      {/* Sidebar */}
      <aside className={`
        fixed top-0 left-0 z-50 h-screen bg-white border-r border-gray-200 transition-all duration-300 ease-in-out shadow-xl
        ${isOpen ? 'w-64' : 'w-16'}
        lg:relative lg:z-auto
      `}>
        <div className="flex flex-col h-full">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-gray-200">
            {isOpen && (
              <div className="flex items-center space-x-3">
                <div className="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                  <Shield className="w-5 h-5 text-white" />
                </div>
                <span className="font-bold text-gray-900 text-lg">PolicyZen</span>
              </div>
            )}
            
            <Button
              variant="ghost"
              size="sm"
              onClick={onToggle}
              className="p-2 hover:bg-gray-100 transition-colors"
              data-testid="sidebar-toggle-btn"
            >
              {isOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </Button>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-2 overflow-y-auto">
            {menuItems.map((item) => {
              const Icon = item.icon;
              const active = isActive(item.path);
              
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  className={`
                    group flex items-center rounded-xl transition-all duration-200 relative overflow-hidden
                    ${isOpen ? 'px-4 py-3' : 'px-3 py-3 justify-center'}
                    ${active 
                      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' 
                      : 'hover:bg-gray-50 text-gray-700 hover:text-gray-900'
                    }
                  `}
                  data-testid={`nav-${item.name.toLowerCase().replace(/\s+/g, '-')}`}
                >
                  {/* Background effect for active state */}
                  {active && (
                    <div className="absolute inset-0 bg-gradient-to-r from-blue-600/90 to-indigo-600/90 opacity-95" />
                  )}
                  
                  <div className={`
                    relative z-10 flex items-center
                    ${isOpen ? 'space-x-3' : 'justify-center'}
                  `}>
                    <div className={`
                      flex items-center justify-center w-6 h-6
                      ${active ? 'text-white' : item.color}
                      group-hover:scale-110 transition-transform duration-200
                    `}>
                      <Icon className="w-5 h-5" />
                    </div>
                    
                    {isOpen && (
                      <>
                        <span className="font-medium text-sm truncate">
                          {item.name}
                        </span>
                        {active && (
                          <ChevronRight className="w-4 h-4 ml-auto opacity-70" />
                        )}
                      </>
                    )}
                  </div>
                  
                  {/* Hover indicator for collapsed state */}
                  {!isOpen && (
                    <div className="absolute left-full ml-6 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                      {item.name}
                      <div className="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                    </div>
                  )}
                </Link>
              );
            })}
          </nav>

          {/* User Profile & Logout */}
          <div className="border-t border-gray-200 p-4">
            {isOpen && (
              <div className="mb-4 p-3 bg-gray-50 rounded-xl">
                <div className="flex items-center space-x-3">
                  <div className="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {user?.name?.charAt(0)?.toUpperCase() || 'U'}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-gray-900 truncate">
                      {user?.name || 'User'}
                    </p>
                    <p className="text-xs text-gray-500 truncate">
                      {user?.role || 'CLIENT'}
                    </p>
                  </div>
                </div>
              </div>
            )}
            
            <Button
              onClick={handleLogout}
              variant="ghost"
              className={`
                w-full text-red-600 hover:text-red-700 hover:bg-red-50 transition-all duration-200
                ${isOpen ? 'justify-start px-4 py-3' : 'px-3 py-3 justify-center'}
              `}
              data-testid="logout-btn"
            >
              <LogOut className="w-5 h-5" />
              {isOpen && <span className="ml-3 text-sm font-medium">Logout</span>}
            </Button>
          </div>
        </div>
      </aside>
    </>
  );
};

export default Sidebar;