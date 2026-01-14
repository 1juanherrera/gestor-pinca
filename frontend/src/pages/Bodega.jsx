import { FaSearch, FaBoxOpen, FaLongArrowAltLeft } from 'react-icons/fa';
import { MdAddCircleOutline, MdOutlineRefresh  } from "react-icons/md";
import { AiFillProduct, AiFillAppstore } from "react-icons/ai";
import { RiFileExcel2Line } from "react-icons/ri";
import { LuAtom } from "react-icons/lu";
import { useNavigate, useParams } from 'react-router-dom';
import { useBodegas } from '../hooks/useBodegas';
import { Loader } from '../components/Loader';
import { TableInventario } from '../components/inventario/TableInventario';
import { useMemo, useState } from 'react';
import { ItemForm } from '../components/inventario/ItemForm';
import { PageTitle } from '../components/PageTitle';

export const Bodega = () => {

  const navigate = useNavigate();
  const { id } = useParams();

  const [tipoFiltro, setTipoFiltro] = useState("");
  const [showForm, setShowForm] = useState(false);
  const { items, refreshItems, isLoadingItems } = useBodegas(id)

  const [filterEstado, setFilterEstado] = useState('');
  const [search, setSearch] = useState('');
  
  const itemsFiltrados = useMemo(() => {
    return tipoFiltro
      ? (items?.inventario || []).filter(item => item.tipo == tipoFiltro)
      : (items?.inventario || []);
  }, [items, tipoFiltro]);

  const filtered = useMemo(() => {
      if (!itemsFiltrados) return [];

      return itemsFiltrados.filter(f => {
          if (filterEstado && f.estado !== filterEstado) return false;
          if (search) {
              const s = search.toLowerCase();
              return (
                  (f.nombre || '').toLowerCase().includes(s) ||
                  (f.codigo || '').toLowerCase().includes(s) ||
                  (f.descripcion || '').toLowerCase().includes(s) ||
                  (String(f.cantidad) || '').toLowerCase().includes(s) ||
                  (String(f.precio_unitario) || '').toLowerCase().includes(s)
              );
          }
          return true;
      });
  }, [itemsFiltrados, filterEstado, search]);

  const filteredColor = (tipo) => {
    switch (tipo) {
      case '0':
        return 'bg-blue-100 text-blue-700 border-blue-300';
      case '1':
        return 'bg-purple-100 text-purple-700 border-purple-300';
      case '2':
        return 'bg-yellow-100 text-yellow-700 border-yellow-300';
      default:
        return 'bg-gray-200 text-gray-700 border-gray-300';
    }
  }
  
  return (
    <div className="ml-65 p-4 bg-gray-100 min-h-screen">
      <PageTitle title="Pinca | Inventario" />
      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div className="flex items-center gap-2">
              <FaBoxOpen className="text-blue-600" size={35} />
              <h1 className="text-xl font-bold text-gray-800 uppercase">
                  Gestión de Inventario
              </h1>
          </div>
          <div className="flex flex-col sm:flex-row gap-3 mb-4">
                <button
                  onClick={() => {
                    refreshItems();
                    setTipoFiltro("");
                  }}
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-500 transition-colors">
                  <MdOutlineRefresh size={20} />
                  Actualizar
              </button>
              <button
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-800 transition-colors">
                  <RiFileExcel2Line  size={20} />
                  Insertar Excel
              </button>
              <button
                  onClick={() => {
                    navigate(-1);
                  }}
                  className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-300 text-black rounded-lg hover:bg-gray-400 transition-colors">
                  <FaLongArrowAltLeft size={20} />
                  Volver
              </button>
          </div>
      </div>
      
      {isLoadingItems && (
        <Loader message="cargando..."/>
      )}

      <div className="bg-white rounded-lg shadow-sm p-4 mb-2">
        <div className="flex flex-wrap items-center justify-between gap-4">
            {/* Botones de filtro por tipo de ítem */}
            <div className="flex items-center gap-3">
                <button
                    onClick={() => {
                      setTipoFiltro("");
                      setSearch("");
                    }}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-gray-400 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <FaBoxOpen size={20} />
                    Todos
                </button>
                <button
                    onClick={() => {
                      setTipoFiltro("0")
                      setSearch("");
                    }}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-emerald-600 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <AiFillProduct size={20} />
                    Productos
                </button>
                <button
                    onClick={() => {
                      setTipoFiltro("1")
                      setSearch("");
                    }}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-purple-600 hover:text-white cursor-pointer flex items-center gap-2`}  
                >
                    <LuAtom size={20} />
                    Materia Prima
                </button>
                <button
                    onClick={() => {
                      setTipoFiltro("2")
                      setSearch("");
                    }}
                    className={`px-4 py-2 rounded-lg font-medium text-sm uppercase tracking-wide transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg hover:bg-yellow-600 hover:text-white cursor-pointer flex items-center gap-2`}
                >
                    <AiFillAppstore size={20} />
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
                <MdAddCircleOutline size={20} />
                Crear Item
            </button>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white p-4 rounded-lg shadow mb-2">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

              <div className="flex items-center gap-3 w-full md:w-1/2">
                  <h2 className="text-xl font-semibold uppercase">
                    {
                      tipoFiltro === "" ? "Todos" : tipoFiltro === "0" 
                      ? "Productos" : tipoFiltro === "1" 
                      ? "Materia Prima" : tipoFiltro === "2" 
                      ? "Insumos" : ""
                    }
                  </h2>
                  <div className={`px-3 shadow-md py-1 text-sm rounded-full bg-gray-200 text-gray-700 border-gray-300 font-medium`}>
                    <span>
                      {items?.inventario ? items?.inventario.length : 0} items totales
                    </span>
                  </div>
                  <div className={`px-3 py-1 shadow-md ${tipoFiltro === "" ? "hidden": ""} text-sm rounded-full ${filteredColor(tipoFiltro)} font-medium`}>
                    <span>
                      {filtered ? filtered.length : 0} items totales
                    </span>
                  </div>
              </div>

              <div className="flex items-center gap-3 w-full md:w-1/3">
                  <div className="relative w-full">
                      <FaSearch className="absolute left-3 top-3 text-gray-400" />
                      <input className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg" 
                      placeholder="Buscar por nombre o codigo" 
                      value={search} onChange={e => setSearch(e.target.value)} />
                  </div>
              </div>
          </div>

        <TableInventario
          items={filtered}
          setFilterEstado={setFilterEstado}
          refreshItems={refreshItems}
        />
      </div>
      
      {showForm && (
        <ItemForm
          onClose={() => setShowForm(false)}
          refreshItems={refreshItems}
          idBodega={id}
        />
      )}
 

    </div>
  )
}