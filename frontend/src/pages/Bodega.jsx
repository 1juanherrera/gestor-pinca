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
import { MdAddCircleOutline } from "react-icons/md";
import { AiFillProduct, AiFillAppstore } from "react-icons/ai";
import { LuAtom } from "react-icons/lu";
import { useNavigate, useParams } from 'react-router-dom';
import { useBodegas } from '../hooks/useBodegas';
import { Loader } from '../components/Loader';
import { Table } from '../components/inventario/TableInventario';

export const Bodega = () => {

  const navigate = useNavigate();
  const { id } = useParams();

  // const [tipoFiltro, setTipoFiltro] = useState("todos");
  const { items, refreshItems, isLoadingItems, errorItems } = useBodegas(id)
  
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
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-300 text-black rounded-lg hover:bg-gray-500 transition-colors">
                  <FaLongArrowAltLeft className="w-4 h-4" />
                  Volver
              </button>
          </div>
      </div>
      
      {isLoadingItems && (
        <Loader />
      )}

      <div className="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div className="flex flex-wrap items-center justify-between gap-4">
            {/* Botones de filtro por tipo de ítem */}
            <div className="flex items-center gap-3">
                <button
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-gray-400 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <FaBoxOpen size={16} />
                    Todos
                </button>
                <button
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-emerald-600 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <AiFillProduct size={16} />
                    Productos
                </button>
                <button
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-purple-600 hover:text-white cursor-pointer flex items-center gap-2`}  
                >
                    <LuAtom size={16} />
                    Materia Prima
                </button>
                <button
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-yellow-600 hover:text-white cursor-pointer flex items-center gap-2`}
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
            >
                <MdAddCircleOutline size={18} />
                Añadir
            </button>
        </div>
      </div>

      {/* Mensaje para estados vacíos adaptado a la nueva API */}
      {!isLoadingItems && !errorItems && items && items.length === 0 && (
          <div className="bg-white rounded-lg shadow-sm p-12 text-center mt-6 flex flex-col items-center">
              <div className="text-gray-400 mb-4">
                  {/* Selecciona el ícono según el nombre_tipo */}
                  {(() => {
                      if (!items[0]) return null;
                      switch ((items[0].nombre_tipo || '').toLowerCase()) {
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
                  No hay {items[0]?.nombre_tipo ? items[0].nombre_tipo.toLowerCase() + 's' : 'ítems'} disponibles
              </h3>
              <button
                  className="bg-emerald-600 text-white px-6 py-2 rounded-lg mt-2 hover:bg-emerald-700 transition-colors flex items-center gap-2"
              >
                  <MdAddCircleOutline size={20} />
                  Añadir {items[0]?.nombre_tipo ? items[0].nombre_tipo.toLowerCase() : 'ítem'}
              </button>
          </div>
      )}

      <Table
          items={items.inventario}
          isLoading={isLoadingItems}
          error={errorItems}
          refreshItems={refreshItems}
        />
    </div>
  )
}