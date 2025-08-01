import { NavLink } from "react-router-dom";
import { FaBoxOpen, FaCalculator, FaFileInvoice, FaUserTag, FaUserFriends, FaCompressAlt } from "react-icons/fa";
import { HiOutlineViewGridAdd } from "react-icons/hi";
import { BsFileEarmarkBarGraphFill } from "react-icons/bs";
import { IoMdExit } from "react-icons/io";

export const Sidebar = () => {
  return (
    <div className="w-3xs bg-neutral-600 text-white p-4 m-2 rounded-lg shadow-lg h-[97%] fixed">
      <ul className="mt-5 space-y-2">
        <li className="w-full">
          <NavLink
            to="/inventario"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaBoxOpen fontSize={25}/> Inventario</NavLink>
        </li> 
        <li className="w-full"> 
          <NavLink
            to="/formulaciones"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaCalculator fontSize={25}/> Formulaciones</NavLink>
        </li>
        <li className="w-full"> 
          <NavLink
            to="/produccion"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><HiOutlineViewGridAdd fontSize={25}/> Produccion</NavLink>
        </li>
        <li className="w-full">
          <NavLink
            to="/facturacion"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaFileInvoice fontSize={25}/> Facturación</NavLink>
        </li>
        <li className="w-full">
          <NavLink
            to="/proveedores"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaUserTag fontSize={25}/> Proveedores</NavLink>
        </li>
        <li className="w-full">
          <NavLink
            to="/clientes"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaUserFriends fontSize={25}/> Clientes</NavLink>
        </li> 
        <li className="w-full">
          <NavLink
            to="/movimientos"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaCompressAlt fontSize={25}/> Movimientos</NavLink>
        </li>
        <li className="w-full">
          <NavLink
            to="/graficas"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><BsFileEarmarkBarGraphFill fontSize={25}/> Graficas</NavLink>
        </li>
        <li className="w-full mt-20">
          <NavLink
            to="/salir"
            className="flex items-center gap-2 w-full p-3 rounded-xl bg-red-500 hover:bg-red-900 cursor-pointer text-left">
            <IoMdExit size={25}/>Salir</NavLink>
        </li>
      </ul>
    </div>
  );
};