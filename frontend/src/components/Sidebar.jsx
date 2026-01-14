import { NavLink } from "react-router-dom";
import { FaWarehouse , FaCalculator, FaFileInvoice, FaUserTag, FaUserFriends, FaCompressAlt } from "react-icons/fa";
import { HiOutlineViewGridAdd } from "react-icons/hi";
import { TbReportMoney } from "react-icons/tb";
import { IoMdExit } from "react-icons/io";
import { logout } from "../store/session";

export const Sidebar = () => {
  return (
    <div className="w-3xs bg-neutral-600 text-white p-4 m-2 rounded-lg shadow-lg h-[97%] fixed">
      <ul className="mt-5 space-y-2">
        <li className="w-full">
          <NavLink
            to="/"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaWarehouse   fontSize={25}/>Sedes</NavLink>
        </li> 
        <li className="w-full"> 
          <NavLink
            to="/formulaciones"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><FaCalculator fontSize={25}/> Laboratorio</NavLink>
        </li>
        <li className="w-full"> 
          <NavLink
            to="/preparaciones"
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
            to="/costos"
            className={({ isActive }) => `flex items-center gap-2 w-full p-3 rounded-xl cursor-pointer text-left ${ isActive ? 'bg-neutral-900' : 'hover:bg-neutral-900'}`}><TbReportMoney fontSize={25}/> Costos</NavLink>
        </li>
        <li className="w-full mt-15">
          <NavLink
          onClick={logout}
            to="/salir"
            className="flex items-center gap-2 w-full p-3 rounded-xl bg-red-500 hover:bg-red-900 cursor-pointer text-left">
            <IoMdExit size={25}/>Salir</NavLink>
        </li>
      </ul>
    </div>
  );
};