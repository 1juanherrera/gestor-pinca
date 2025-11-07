import { BodegasInstalacion } from "./components/bodegas/BodegasInstalacion";
import { Sidebar } from "./components/Sidebar"
import { Bodega } from "./pages/Bodega";
// import { Clientes } from "./pages/Clientes";
import { Formulaciones } from "./pages/Formulaciones";
import { Home } from "./pages/Home";
import { Inventario } from "./pages/Inventario"
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Proveedores } from "./pages/Proveedores";

export const App = () => {

  return (
    <BrowserRouter>
      <Sidebar />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/inventario" element={<Inventario />} />
         <Route path="/formulaciones" element={<Formulaciones />} />
         <Route path="/instalaciones/bodegas/:id" element={<BodegasInstalacion />} />
         <Route path="/bodegas/:id" element={<Bodega />} />
        {/* <Route path="/clientes" element={<Clientes />} /> */}
        <Route path="/proveedores" element={<Proveedores />} />
      </Routes>
    </BrowserRouter>
  )
}