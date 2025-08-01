import { Sidebar } from "./components/Sidebar"
// import { Clientes } from "./pages/Clientes";
// import { Formulaciones } from "./pages/Formulaciones";
import { Inventario } from "./pages/Inventario"
import { BrowserRouter, Routes, Route } from 'react-router-dom';
// import { Proveedores } from "./pages/Proveedores";

export const App = () => {

  return (
    <BrowserRouter>
      <Sidebar />
      <Routes>
        <Route path="/inventario" element={<Inventario />} />
        {/* <Route path="/formulaciones" element={<Formulaciones />} />
        <Route path="/clientes" element={<Clientes />} />
        <Route path="/proveedores" element={<Proveedores />} /> */}
      </Routes>
    </BrowserRouter>
  )
}