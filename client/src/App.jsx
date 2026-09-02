import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import ProtectedRoute from './components/ProtectedRoute';

// Auth
import Login    from './pages/Login';
import Register from './pages/Register';

// Customer
import Home     from './pages/customer/Home';
import Shop     from './pages/customer/Shop';
import Product  from './pages/customer/Product';
import Cart     from './pages/customer/Cart';
import Checkout from './pages/customer/Checkout';
import Orders   from './pages/customer/Orders';
import Wishlist from './pages/customer/Wishlist';

// Admin
import AdminDashboard  from './pages/admin/Dashboard';
import AdminProducts   from './pages/admin/Products';
import AdminCategories from './pages/admin/Categories';
import AdminOrders     from './pages/admin/Orders';
import AdminUsers      from './pages/admin/Users';
import AdminReviews    from './pages/admin/Reviews';
import AdminReports    from './pages/admin/Reports';

// Cashier
import CashierPOS       from './pages/cashier/POS';
import CashierSales     from './pages/cashier/Sales';
import CashierCustomers from './pages/cashier/Customers';
import CashierReceipt   from './pages/cashier/Receipt';
import CashierLogs      from './pages/cashier/Logs';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <CartProvider>
          <Routes>
            {/* Public */}
            <Route path="/"         element={<Home />} />
            <Route path="/login"    element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/shop"     element={<Shop />} />
            <Route path="/shop/:id" element={<Product />} />

            {/* Customer (any logged-in user can view shop but orders/cart require customer role) */}
            <Route element={<ProtectedRoute roles={['customer']} />}>
              <Route path="/cart"     element={<Cart />} />
              <Route path="/checkout" element={<Checkout />} />
              <Route path="/orders"   element={<Orders />} />
              <Route path="/wishlist" element={<Wishlist />} />
            </Route>

            {/* Admin */}
            <Route element={<ProtectedRoute roles={['admin']} />}>
              <Route path="/admin"              element={<AdminDashboard />} />
              <Route path="/admin/products"     element={<AdminProducts />} />
              <Route path="/admin/categories"   element={<AdminCategories />} />
              <Route path="/admin/orders"       element={<AdminOrders />} />
              <Route path="/admin/users"        element={<AdminUsers />} />
              <Route path="/admin/reviews"      element={<AdminReviews />} />
              <Route path="/admin/reports"      element={<AdminReports />} />
            </Route>

            {/* Cashier */}
            <Route element={<ProtectedRoute roles={['cashier', 'admin']} />}>
              <Route path="/cashier"           element={<CashierPOS />} />
              <Route path="/cashier/sales"     element={<CashierSales />} />
              <Route path="/cashier/customers" element={<CashierCustomers />} />
              <Route path="/cashier/receipt/:order_id" element={<CashierReceipt />} />
              <Route path="/cashier/logs"      element={<CashierLogs />} />
            </Route>

            {/* Catch-all */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </CartProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
