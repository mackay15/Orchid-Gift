import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function ProtectedRoute({ roles }) {
  const { isLoggedIn, user } = useAuth();

  if (!isLoggedIn) {
    return <Navigate to="/login" replace />;
  }

  if (roles && !roles.includes(user?.role)) {
    // Redirect to appropriate dashboard
    if (user?.role === 'admin')    return <Navigate to="/admin"    replace />;
    if (user?.role === 'cashier')  return <Navigate to="/cashier"  replace />;
    if (user?.role === 'customer') return <Navigate to="/shop"     replace />;
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}
