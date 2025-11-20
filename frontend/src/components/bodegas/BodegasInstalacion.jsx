import { FaWarehouse, FaBoxOpen, FaEdit, FaCity, FaMapMarkerAlt, FaPhone, FaPlus, FaLongArrowAltLeft } from "react-icons/fa";
import { MdDelete } from "react-icons/md";
import { NavLink, useParams, useNavigate } from "react-router-dom";
import { useBodegas } from "../../hooks/useBodegas";
import { useState } from "react";
import BodegaForm from "./BodegaForm";
import { useToast } from "../../hooks/useToast";
import { Toast } from "../Toast";

export const BodegasInstalacion = () => {

  const { id } = useParams();
  const navigate = useNavigate();
  const {
    data,
    create,
    isCreating,
    createError,
    refreshData,
    remove,
    isDeleting,
    deleteError,
    update
  } = useBodegas(id);

    const {
        toastVisible,
        toastMessage,
        toastType,
        eventToast,
        setToastVisible
    } = useToast();

  const instalacion = Array.isArray(data) ? null : data;
  const bodegas = Array.isArray(data) ? data : data?.bodegas ?? [];
  
  const [editingBodega, setEditingBodega] = useState(null);
  const [showCreate, setShowCreate] = useState(false);
  
  const [form, setForm] = useState({
    nombre: '',
    descripcion: '',
    estado: '1',
    instalaciones_id: id,
  })

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value, instalaciones_id: id }));
  }

  const handleDelete = (id_bodegas, nombre) => {
      if (window.confirm(`¿Seguro que deseas eliminar la bodega: ${nombre.toUpperCase()}?`)) {
          remove(id_bodegas); 
          eventToast(`${nombre} eliminada correctamente.`, "error");
      }
  }


  return (
    <div className="ml-65 p-6 bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div className="flex flex-col sm:flex-row gap-3 mb-4">
          </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
              <span className="flex items-center gap-2">
                  <FaCity className="text-gray-400" />
                  <strong>Ciudad:</strong> {instalacion?.ciudad?.trim() || 'N/A'}
              </span>
              <span className="flex items-center gap-2">
                  <FaMapMarkerAlt className="text-gray-400" />
                  <strong>Dirección:</strong> {instalacion?.direccion || 'N/A'}
              </span>
              <span className="flex items-center gap-2">
                  <FaPhone className="text-gray-400" />
                  <strong>Teléfono:</strong> {instalacion?.telefono || 'N/A'}
              </span>
          </div>
      </div>

      <div className="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-400 pb-4 mb-6 mt-6">
        <h2 className="text-xl font-bold flex items-center gap-3 text-gray-700 uppercase">
          <FaBoxOpen className="text-blue-500" size={20} />
          {instalacion?.nombre} ({bodegas.length}) 
        </h2>
          <div className="flex gap-2">
            <button
            onClick={() => {
              setShowCreate(true);
              setEditingBodega(null); 
              setForm({
                  nombre: '',
                  descripcion: '',
                  estado: '1',
                  instalaciones_id: id,
              });
            }}
            className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <FaPlus size={16} /> Agregar Nueva Bodega
          </button>
          <button 
          onClick={() => {
            navigate(-1);
          }}
          className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-300 text-black rounded-lg hover:bg-gray-400 transition-colors">
            <FaLongArrowAltLeft className="w-4 h-4" />
            Volver
          </button>
        </div>
      </div>
          
      {toastVisible && (
          <Toast
              message={toastMessage} 
              type={toastType}
              onClose={() => setToastVisible(false)}
          />
      )}
      {showCreate && (
        <BodegaForm
          onSubmit={refreshData}
          handleChange={handleChange}
          setShowCreate={setShowCreate}
          create={create}
          isCreating={isCreating}
          createError={createError}
          form={form}
          setForm={setForm}
          update={update}
          editingBodega={editingBodega}
          setEditingBodega={setEditingBodega}
          eventToast={eventToast}
        />
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {bodegas.length > 0 ? (
          bodegas.map((bodega, index) => (
            <div 
                key={`${bodega.id_bodegas}-${index}`}
                className={`${bodega.estado === "1" ? 'bg-white' : 'bg-gray-200'} p-5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-blue-500 flex flex-col justify-between`}
            >
              <div>
                <div className="flex items-center justify-between mb-3">
                    <h3 className="text-lg font-extrabold text-gray-900 uppercase truncate">
                        {bodega.nombre}
                    </h3>
                    <span 
                        className={`inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold ${
                            bodega.estado === "1" ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'
                        }`}
                    >
                        {bodega.estado === "1" ? 'ACTIVA' : 'INACTIVA'}
                    </span>
                </div>
                
                <p className="text-sm text-gray-600 mb-4 h-12 overflow-hidden">
                    <span className="font-semibold text-gray-800">Descripción: </span>
                    {bodega.descripcion || 'Sin descripción detallada.'}
                </p>
              </div>

              <div className="flex gap-3 pt-3 border-t border-gray-100">
                <button
                    title="Eliminar bodega"
                    type="button"
                    onClick={e => {
                        e.preventDefault();
                        handleDelete(bodega.id_bodegas, bodega.nombre);
                    }}
                    className="flex-1 p-2 cursor-pointer flex items-center justify-center gap-1 text-red-600 bg-red-50 rounded-lg shadow-lg border border-red-100 hover:bg-red-100 transition-colors disabled:opacity-50"
                    disabled={isDeleting}
                >
                    <MdDelete size={18} />
                    Eliminar
                </button>
                {/* Botón de Editar */}
                <button
                    title="Editar bodega"
                    type="button"
                    onClick={e => {
                        e.preventDefault();
                        setForm({
                            nombre: bodega.nombre,
                            descripcion: bodega.descripcion,
                            estado: bodega.estado.toString(),
                            instalaciones_id: id,
                        });
                        setEditingBodega(bodega);
                        setShowCreate(true);
                    }}
                    className="flex-1 cursor-pointer p-2 flex items-center justify-center gap-1 text-blue-600 bg-blue-50 rounded-lg shadow-lg border border-blue-100 hover:bg-blue-100 transition-colors"
                >
                    <FaEdit size={16} />
                    Editar
                </button>
                <NavLink
                    to={`/bodegas/${bodega.id_bodegas}`}
                    className="flex-1 p-2 cursor-pointer flex items-center justify-center gap-1 text-gray-600 bg-gray-100 rounded-lg shadow-lg border border-gray-200 hover:bg-gray-200 transition-colors disabled:opacity-50"
                    title="Ver Detalles"
                >
                    <FaWarehouse size={16} /> 
                    Inventario
                </NavLink>
              </div>
            </div>
          ))
        ) : (
          <div className="col-span-full p-8 bg-white rounded-xl shadow-inner text-center border-2 border-dashed border-gray-300">
             <FaBoxOpen className="text-gray-400 mx-auto mb-3" size={40} />
             <p className="text-lg font-semibold text-gray-600">No hay bodegas registradas en esta instalación.</p>
             <p className="text-sm text-gray-500">Haz clic en "Agregar Nueva Bodega" para empezar.</p>
          </div>
        )}
      </div>

      {/* Manejo de Errores */}
      {deleteError && (
        <p className="text-red-500 mt-6 p-3 bg-red-100 border border-red-300 rounded-lg">
          Error al eliminar: {deleteError.message || "No se pudo eliminar la bodega. Inténtalo de nuevo."}
        </p>
      )}
    </div>
  )
}