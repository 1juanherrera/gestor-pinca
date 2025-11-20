import {
    FaPlus,
    FaSearch,
    FaUsers,
    FaUser,
    FaEye,
    FaEdit,
    FaTrash,
    FaBuilding,
    FaPhone,
    FaEnvelope,
    FaBoxOpen,
    FaLongArrowAltLeft
} from 'react-icons/fa';
import { MdAddCircleOutline, MdOutlineRefresh  } from "react-icons/md";
import { AiFillProduct, AiFillAppstore } from "react-icons/ai";
import { LuAtom } from "react-icons/lu";
import { useNavigate, useParams } from 'react-router-dom';
import { useBodegas } from '../hooks/useBodegas';
import { Loader } from '../components/Loader';
import { TableInventario } from '../components/inventario/TableInventario';
import { useState } from 'react';
import { ItemForm } from '../components/inventario/ItemForm';

export const Bodega = () => {

  const navigate = useNavigate();
  const { id } = useParams();

  const [tipoFiltro, setTipoFiltro] = useState("");
  const [showForm, setShowForm] = useState(false);
  const { items, refreshItems, isLoadingItems } = useBodegas(id)

  const itemsFiltrados = tipoFiltro
    ? items.inventario?.filter(item => item.tipo == tipoFiltro)
    : items.inventario;
  
  return (
    <div className="ml-65 p-4 bg-gray-100 min-h-screen">
      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div className="flex items-center gap-2">
              <FaBoxOpen className="text-blue-600 w-6 h-6" />
              <h1 className="text-xl font-bold text-gray-800">
                  Gestión de Inventario
              </h1>
          </div>
          <div className="flex flex-col sm:flex-row gap-3 mb-4">
              <button
                  onClick={() => {
                    navigate(-1);
                  }}
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-300 text-black rounded-lg hover:bg-gray-400 transition-colors">
                  <FaLongArrowAltLeft className="w-4 h-4" />
                  Volver
              </button>
                <button
                  onClick={() => {
                    refreshItems();
                    setTipoFiltro("");
                  }}
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-500 transition-colors">
                  <MdOutlineRefresh className="w-4 h-4" />
                  Actualizar
              </button>
          </div>
      </div>
      
      {isLoadingItems && (
        <Loader message="cargando..."/>
      )}

      <div className="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div className="flex flex-wrap items-center justify-between gap-4">
            {/* Botones de filtro por tipo de ítem */}
            <div className="flex items-center gap-3">
                <button
                    onClick={() => setTipoFiltro("")}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-gray-400 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <FaBoxOpen size={16} />
                    Todos
                </button>
                <button
                    onClick={() => setTipoFiltro("0")}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-emerald-600 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <AiFillProduct size={16} />
                    Productos
                </button>
                <button
                    onClick={() => setTipoFiltro("1")}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-purple-600 hover:text-white cursor-pointer flex items-center gap-2`}  
                >
                    <LuAtom size={16} />
                    Materia Prima
                </button>
                <button
                    onClick={() => setTipoFiltro("2")}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-yellow-600 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <AiFillAppstore size={16} />
                    Insumos
                </button>
            </div>

            {/* Botón de añadir nuevo ítem */}
            <button
                onClick={() => setShowForm(true)}
                className="
                    px-6 py-2 rounded-lg font-medium text-sm uppercase tracking-wide
                    transition-all duration-200 transform hover:scale-105
                    shadow-md hover:shadow-lg cursor-pointer flex items-center gap-2
                    bg-emerald-600 text-white hover:bg-emerald-700"
            >
                <MdAddCircleOutline size={18} />
                Crear Item
            </button>
        </div>
      </div>
      
      {showForm && (
        <ItemForm
          onClose={() => setShowForm(false)}
          refreshItems={refreshItems}
        />
      )}
 
      <TableInventario
          items={itemsFiltrados}
          refreshItems={refreshItems}
        />
    </div>
  )
}