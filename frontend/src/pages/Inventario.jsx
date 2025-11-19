// import { Table } from "../components/inventario/TableInventario"; 
// import { ItemForm } from "../components/inventario/ItemForm"; 
// import { SearchBar } from "../components/SearchBar";
import { MdAddCircleOutline } from "react-icons/md";
import { AiFillProduct, AiFillAppstore } from "react-icons/ai";
import { LuAtom } from "react-icons/lu";
import { FaBoxOpen } from "react-icons/fa";
import { useItems } from '../hooks/useItems';
import { Table } from "../components/inventario/TableInventario";
import { useState } from "react";

export const Inventario = () => {
    
    const { data, isLoading, error } = useItems();
    const [tipoFiltro, setTipoFiltro] = useState("todos");

    const productosFiltrados = tipoFiltro === "todos"
        ? data
        : data.filter(item => (item.nombre_tipo || "").toLowerCase() === tipoFiltro);

    if (isLoading) return <div>Cargando...</div>;
    if (error) return <div>Error: {error.message}</div>;

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            <div className="mb-4 flex items-center gap-2">
                    <FaBoxOpen className="text-blue-500" size={25} />
                <div>
                    <h5 className="text-xl font-bold text-gray-800 mb-2 flex items-center">
                         Gestión de Inventario
                     </h5>                       
                 </div>
             </div> 

            <div className="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    {/* Botones de filtro por tipo de ítem */}
                    <div className="flex items-center gap-3">
                        <button
                            className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-gray-400 hover:text-white cursor-pointer flex items-center gap-2 ${tipoFiltro === "todos" ? "bg-gray-400 text-white" : ""}`}
                            onClick={() => setTipoFiltro("todos")}
                        >
                            <FaBoxOpen size={16} />
                            Todos
                        </button>
                        <button
                            className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-emerald-600 hover:text-white cursor-pointer flex items-center gap-2 ${tipoFiltro === "producto" ? "bg-emerald-600 text-white" : ""}`}
                            onClick={() => setTipoFiltro("producto")}
                        >
                            <AiFillProduct size={16} />
                            Productos
                        </button>
                        <button
                            className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-purple-600 hover:text-white cursor-pointer flex items-center gap-2 ${tipoFiltro === "materia prima" ? "bg-purple-600 text-white" : ""}`}
                            onClick={() => setTipoFiltro("materia prima")}
                        >
                            <LuAtom size={16} />
                            Materia Prima
                        </button>
                        <button
                            className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-yellow-600 hover:text-white cursor-pointer flex items-center gap-2 ${tipoFiltro === "insumo" ? "bg-yellow-600 text-white" : ""}`}
                            onClick={() => setTipoFiltro("insumo")}
                        >
                            <AiFillAppstore size={16} />
                            Insumos
                        </button>
                    </div>

                    {/* Botón de añadir nuevo ítem */}
                    <button
                        className="
                            px-6 py-2 rounded-lg font-medium text-sm uppercase tracking-wide
                            transition-all duration-200 transform hover:scale-105
                            shadow-md hover:shadow-lg cursor-pointer flex items-center gap-2
                            bg-emerald-600 text-white hover:bg-emerald-700"
                        onClick={() => setShowForm(true)}
                    >
                        <MdAddCircleOutline size={18} />
                        Añadir
                    </button>
                </div>
            </div>

            {/* Mensaje para estados vacíos adaptado a la nueva API */}
            {!isLoading && !error && data && data.length === 0 && (
                <div className="bg-white rounded-lg shadow-sm p-12 text-center mt-6 flex flex-col items-center">
                    <div className="text-gray-400 mb-4">
                        {/* Selecciona el ícono según el nombre_tipo */}
                        {(() => {
                            if (!data[0]) return null;
                            switch ((data[0].nombre_tipo || '').toLowerCase()) {
                                case 'producto':
                                    return <AiFillProduct size={48} />;
                                case 'materia prima':
                                    return <LuAtom size={48} />;
                                case 'insumo':
                                    return <AiFillAppstore size={48} />;
                                default:
                                    return <FaBoxOpen size={48} />;
                            }
                        })()}
                    </div>
                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                        No hay {data[0]?.nombre_tipo ? data[0].nombre_tipo.toLowerCase() + 's' : 'ítems'} disponibles
                    </h3>
                    <button
                        className="bg-emerald-600 text-white px-6 py-2 rounded-lg mt-2 hover:bg-emerald-700 transition-colors flex items-center gap-2"
                        onClick={() => setShowForm && setShowForm(true)}
                    >
                        <MdAddCircleOutline size={20} />
                        Añadir {data[0]?.nombre_tipo ? data[0].nombre_tipo.toLowerCase() : 'ítem'}
                    </button>
                </div>
            )}


                <Table 
                    products={productosFiltrados}
                    isLoading={isLoading}
                    error={error}
                 />
    </div>
  );
}