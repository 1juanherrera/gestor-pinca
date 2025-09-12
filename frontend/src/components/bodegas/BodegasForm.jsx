import { useState } from "react";

export const BodegaForm = ({
  initialData = {},
  onSubmit,
  isLoading = false,
  error = null,
  onCancel,
}) => {
  const [form, setForm] = useState({
    nombre: initialData.nombre || "",
    descripcion: initialData.descripcion || "",
    estado: initialData.estado || "1",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(form);
  };

  return (
    <form
      onSubmit={handleSubmit}
      className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md"
    >
      <h2 className="text-xl font-bold mb-4">Bodega</h2>
      <div className="mb-3">
        <label className="block text-sm font-medium text-gray-700">
          Nombre
        </label>
        <input
          name="nombre"
          value={form.nombre}
          onChange={handleChange}
          required
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"
        />
      </div>
      <div className="mb-3">
        <label className="block text-sm font-medium text-gray-700">
          Descripción
        </label>
        <input
          name="descripcion"
          value={form.descripcion}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"
        />
      </div>
      <div className="mb-3">
        <label className="block text-sm font-medium text-gray-700">
          Estado
        </label>
        <select
          name="estado"
          value={form.estado}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 block w-full p-2"
        >
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>
      <div className="flex gap-2 mt-4">
        <button
          type="submit"
          className="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors font-semibold"
          disabled={isLoading}
        >
          {isLoading ? "Guardando..." : "Guardar"}
        </button>
        {onCancel && (
          <button
            type="button"
            className="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition-colors font-semibold"
            onClick={onCancel}
          >
            Cancelar
          </button>
        )}
      </div>
      {error && (
        <p className="text-red-500 mt-2">
          Error: {error.message || "No se pudo guardar"}
        </p>
      )}
    </form>
  );
};