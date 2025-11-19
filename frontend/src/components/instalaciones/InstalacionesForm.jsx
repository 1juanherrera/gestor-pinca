import { FaBuilding } from "react-icons/fa";

export default function InstalacionesForm({ 
  onSubmit, 
  eventToast, 
  handleChange, 
  setShowCreate, 
  create, 
  isCreating, 
  createError, 
  empresas, 
  form, 
  setForm }) {

  const handleSubmit = (e) => {
    e.preventDefault();
    create(form, {
      onSuccess: () => {
        onSubmit();
        setShowCreate(false);
        setForm({
          nombre: '',
          descripcion: '',
          ciudad: '',
          direccion: '',
          telefono: '',
          id_empresa: '',
        });
        eventToast("Sede creada correctamente", "success");
      }
    });
  };
  

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
      onClick={() => setShowCreate(false)}>
      <form onClick={e => e.stopPropagation()} onSubmit={handleSubmit} className="w-110 mx-auto bg-white p-6 rounded-xl shadow space-y-4">
      <div className="flex items-center space-x-2 mb-4">
        <FaBuilding className="h-6 w-6 text-blue-500" />
        <h1 className="text-2xl font-bold">Nueva Sede</h1>
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Nombre</label>
        <input
          type="text"
          name="nombre"
          value={form.nombre}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
           focus:outline-none focus:ring-2 block w-full p-2"/>
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Descripción</label>
        <input
          type="text"
          name="descripcion"
          value={form.descripcion}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
           focus:outline-none focus:ring-2 block w-full p-2"
          
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Ciudad</label>
        <input
          type="text"
          name="ciudad"
          value={form.ciudad}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
           focus:outline-none focus:ring-2 block w-full p-2"
          
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Dirección</label>
        <input
          type="text"
          name="direccion"
          value={form.direccion}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
           focus:outline-none focus:ring-2 block w-full p-2"
          
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Teléfono</label>
        <input
          type="text"
          name="telefono"
          value={form.telefono}
          onChange={handleChange}
          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
           focus:outline-none focus:ring-2 block w-full p-2"
          
        />
      </div>
      <div>
        <label className="block text-sm font-medium text-gray-700">Empresa</label>
          <select
            name="id_empresa"
            value={form.id_empresa}
            onChange={handleChange}
            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
              focus:outline-none focus:ring-2 block w-full p-2"
          >
            <option value="">Seleccionar empresa</option>
            {empresas.map((e, index) => (
              <option key={`${e.id_empresa}-${index}`} value={e.id_empresa}>
                {e.razon_social}
              </option>
            ))}
          </select>
      </div>
      <button
        type="submit"
        disabled={!form.nombre || !form.ciudad || !form.direccion || !form.id_empresa}
        className="w-full py-2 disabled:opacity-50 cursor-pointer px-4 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition"
      >
        {isCreating ? "Guardando..." : "Crear Instalación"}
      </button>
      {createError && (
        <p className="text-red-500">
          Error: {createError.message || "No se pudo crear"}
        </p>
      )}
    </form>
    </div>
  )
}