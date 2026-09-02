import { createContext, useContext, useState, useEffect, useCallback } from 'react';

const CartContext = createContext(null);

const CART_KEY = 'orchid_cart';

export function CartProvider({ children }) {
  const [items, setItems] = useState(() => {
    try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
    catch { return []; }
  });

  // Persist cart to localStorage
  useEffect(() => {
    localStorage.setItem(CART_KEY, JSON.stringify(items));
  }, [items]);

  const addItem = useCallback((product, quantity = 1) => {
    setItems(prev => {
      const existing = prev.find(i => i.product_id === product.product_id);
      if (existing) {
        const newQty = Math.min(existing.quantity + quantity, product.stock_quantity);
        return prev.map(i => i.product_id === product.product_id ? { ...i, quantity: newQty } : i);
      }
      return [...prev, { ...product, quantity: Math.min(quantity, product.stock_quantity) }];
    });
  }, []);

  const removeItem = useCallback((product_id) => {
    setItems(prev => prev.filter(i => i.product_id !== product_id));
  }, []);

  const updateQty = useCallback((product_id, quantity) => {
    if (quantity <= 0) { removeItem(product_id); return; }
    setItems(prev => prev.map(i => i.product_id === product_id ? { ...i, quantity } : i));
  }, [removeItem]);

  const clearCart = useCallback(() => {
    setItems([]);
  }, []);

  const total     = items.reduce((sum, i) => sum + parseFloat(i.price) * i.quantity, 0);
  const itemCount = items.reduce((sum, i) => sum + i.quantity, 0);

  return (
    <CartContext.Provider value={{ items, addItem, removeItem, updateQty, clearCart, total, itemCount }}>
      {children}
    </CartContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components
export const useCart = () => {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used inside CartProvider');
  return ctx;
};
