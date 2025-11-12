import { FaWarehouse, FaBoxOpen, FaEdit } from "react-icons/fa";
import { MdDelete } from "react-icons/md";
import { NavLink, useParams } from "react-router-dom";
import { useBodegas } from "../../hooks/useBodegas";
import { useState } from "react";
import BodegaForm from "./BodegaForm";

export const BodegasInstalacion = () => {

  const { id } = useParams();
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

  const handleDelete = (id) => {
    if (window.confirm("¿Seguro que deseas eliminar esta instalación?")) {
      remove(id);
    }
  }

  return (
    <div className="ml-65 p-4 bg-gray-100 min-h-screen">
      <div className="flex items-start justify-between mb-4">
        <div className="max-w-xl">
        <h2 className="text-xl font-bold flex items-center gap-2 mb-2">
          <FaWarehouse className="text-blue-400" />
          BODEGAS {data?.nombre?.toUpperCase() || id}
        </h2>
        <div className="mb-2">
          <span className="font-semibold text-gray-700">Descripción:</span>
          <span className="ml-2 text-gray-800">{data?.descripcion}</span>
        </div>
        <div>
          <span className="font-semibold text-gray-700">Ciudad:</span>
          <span className="ml-2 text-gray-800">{data?.ciudad}</span>
        </div>
      </div>
        <button
          onClick={() => setShowCreate(true)}
          className="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors"
        >
          <FaBoxOpen size={14} /> Nueva Bodega
        </button>
      </div>
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
        />
      )}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {bodegas.length > 0 ? (
          bodegas.map((bodega, index) => (
            <NavLink
              to={`/bodegas/${bodega.id_bodegas}`}
              key={`${bodega.id_bodegas}-${index}`}
              className="bg-white rounded-lg shadow-md p-4 border border-gray-200 scale-hover relative"
            >
              <h3 className="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                <FaBoxOpen className="text-purple-400" />
                {bodega.nombre.toUpperCase()}
              </h3>
              <p className="text-sm text-gray-600 mb-2">{bodega.descripcion}</p>
              <div className="flex gap-2 absolute top-2 right-2">
                <button
                  title="Eliminar bodega"
                  type="button"
                  onClick={e => {
                    e.preventDefault();
                    e.stopPropagation();
                    handleDelete(bodega.id_bodegas);
                  }}
                  className="ml-auto cursor-pointer p-1 rounded hover:bg-red-100 transition-colors"
                  disabled={isDeleting}
                >
                  <MdDelete className="text-red-500" size={21} />
                </button>
                <button
                  className="ml-auto p-1 rounded hover:bg-blue-100 transition-colors cursor-pointer"
                  title="Editar bodega"
                  type="button"
                  onClick={e => {
                    e.preventDefault();
                    e.stopPropagation();
                    setForm({
                      nombre: bodega.nombre,
                      descripcion: bodega.descripcion,
                      estado: bodega.estado.toString(),
                      instalaciones_id: id,
                    });
                    setEditingBodega(bodega);
                    setShowCreate(true);
                  }}
                >
                  <FaEdit className="text-blue-500" size={21} />
                </button>
              </div>
            </NavLink>
          ))
        ) : (
          <p className="text-gray-500">No hay bodegas registradas.</p>
        )}
      </div>
      {deleteError && (
        <p className="text-red-500 mt-2">
          Error: {deleteError.message || "No se pudo eliminar"}
        </p>
      )}
    </div>
  )
}