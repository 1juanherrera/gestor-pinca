import { BodegasInstalacion } from "./components/bodegas/BodegasInstalacion";
import { Sidebar } from "./components/Sidebar"
import { Bodega } from "./pages/Bodega";
import { Clientes } from "./pages/Clientes";
import { Formulaciones } from "./pages/Formulaciones";
import { Home } from "./pages/Home";
import { Inventario } from "./pages/Inventario"
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { Proveedores } from "./pages/Proveedores";
import { PagosClientes } from "./pages/PagosClientes";
import { Facturacion } from "./pages/Facturacion";
import { Movimientos } from "./pages/Movimientos";
import { Preparaciones } from "./pages/Preparaciones";
import { Login } from "./pages/Login";

import ProtectedRoute from "./routes/ProtectedRoute";

const AppContent = () => {

  const location = useLocation();
  const hideSidebar = location.pathname === "/login";

  return (
    <>
      {/* Ocultar Sidebar solo en /login */}
      {!hideSidebar && <Sidebar />}

      <Routes>

        {/* Rutas públicas */}
        <Route path="/login" element={<Login />} />

        {/* Rutas protegidas */}
        <Route path="/" element={
          <ProtectedRoute>
            <Home />
          </ProtectedRoute>
        } />

        <Route path="/inventario" element={
          <ProtectedRoute>
            <Inventario />
          </ProtectedRoute>
        } />

        <Route path="/formulaciones" element={
          <ProtectedRoute>
            <Formulaciones />
          </ProtectedRoute>
        } />

        <Route path="/instalaciones/bodegas/:id" element={
          <ProtectedRoute>
            <BodegasInstalacion />
          </ProtectedRoute>
        } />

        <Route path="/bodegas/:id" element={
          <ProtectedRoute>
            <Bodega />
          </ProtectedRoute>
        } />

        <Route path="/clientes" element={
          <ProtectedRoute>
            <Clientes />
          </ProtectedRoute>
        } />

        <Route path="/proveedores" element={
          <ProtectedRoute>
            <Proveedores />
          </ProtectedRoute>
        } />

        <Route path="/pagos-clientes" element={
          <ProtectedRoute>
            <PagosClientes />
          </ProtectedRoute>
        } />

        <Route path="/facturacion" element={
          <ProtectedRoute>
            <Facturacion />
          </ProtectedRoute>
        } />

        <Route path="/movimientos" element={
          <ProtectedRoute>
            <Movimientos />
          </ProtectedRoute>
        } />

        <Route path="/preparaciones" element={
          <ProtectedRoute>
            <Preparaciones />
          </ProtectedRoute>
        } />

        {/* Si no está el token → Navigate a /error */}
        <Route path="/error" element={<h1>Error: No autorizado</h1>} />

      </Routes>
    </>
  );
};

export const App = () => {
  return (
    <BrowserRouter>
      <AppContent />
    </BrowserRouter>
  );
};
