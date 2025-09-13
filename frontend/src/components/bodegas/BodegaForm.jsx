import { useEffect } from "react";
import { FaBoxOpen } from "react-icons/fa";

export default function BodegaForm({ 
  onSubmit, 
  handleChange, 
  setShowCreate, 
  create, 
  isCreating, 
  createError, 
  form, 
  update,
  setForm,
  editingBodega,
  setEditingBodega }) {

  useEffect(() => {
    if (editingBodega) {
      setForm(editingBodega);
    } else {
      setForm({
        nombre: "",
        descripcion: "",
        estado: "1"
      });
    }
  }, [editingBodega, setForm]);

  const handleSubmit = (e) => {
    e.preventDefault();

    if (editingBodega) {
      update({ id: editingBodega.id_bodegas, data: form }, {
        onSuccess: () => {
          onSubmit();
          setShowCreate(false);
          setEditingBodega(null);
        }
      });
    } else {
      create(form, {
        onSuccess: () => {
          onSubmit();
          setShowCreate(false);
        }
      });
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
      onClick={() => setShowCreate(false)}>
      <form onClick={e => e.stopPropagation()} onSubmit={handleSubmit} className="w-110 mx-auto bg-white p-6 rounded-xl shadow space-y-4">
        <div className="flex items-center space-x-2 mb-4">
          <FaBoxOpen className="h-6 w-6 text-purple-500" />
          <h1 className="text-2xl font-bold">{!editingBodega ? "Nueva Bodega" : "Editar Bodega"}</h1>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700">Nombre</label>
          <input
            type="text"
            name="nombre"
            value={form.nombre}
            onChange={handleChange}
            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"/>
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700">Descripción</label>
          <input
            type="text"
            name="descripcion"
            value={form.descripcion}
            onChange={handleChange}
            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700">Estado</label>
          <select
            name="estado"
            value={form.estado}
            onChange={handleChange}
            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"
          >
            <option value="1">ACTIVO</option>
            <option value="0">INACTIVO</option>
          </select>
        </div>
        <button
          type="submit"
          disabled={!form.nombre && !form.descripcion}
          className="w-full cursor-pointer py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition"
        >
          {
          isCreating
            ? "Guardando..."
            : editingBodega
              ? "Actualizar Bodega"
              : "Crear Bodega"
          }
        </button>
        {createError && (
          <p className="text-red-500">Error: {createError.message || "No se pudo guardar"}</p>
        )}
      </form>
    </div>
  );
}