import { FaBoxOpen, FaMapMarkerAlt, FaCity, FaPhoneAlt, FaBuilding, FaEdit, FaWarehouse } from "react-icons/fa";
import { MdDelete, MdDescription   } from "react-icons/md";
import InstalacionesForm from "../components/instalaciones/InstalacionesForm";
import { useState } from "react";
import { useInstalaciones } from "../hooks/useInstalaciones";
import { IoCloseSharp } from "react-icons/io5";
import Pincalogo from "../assets/pincalogo.png";
import { NavLink } from "react-router-dom";
import { useEmpresa } from "../hooks/useEmpresa";
import { useToast } from "../hooks/useToast";
import { Toast } from "../components/Toast";
import { Loader } from "../components/Loader";
import { PageTitle } from "../components/PageTitle";


export const Home = () => {

    const { data: empresas } = useEmpresa();

    const {
        toastVisible,
        toastMessage,
        toastType,

        eventToast,
        setToastVisible
    } = useToast();

    const [form, setForm] = useState({
        nombre: '',
        descripcion: '',
        ciudad: '',
        direccion: '',
        telefono: '',
        id_empresa: '',
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ 
        ...prev, 
        [name]: value 
        }))
    }

    const { 
        data: instalaciones, 
        isLoading, 
        error, 
        refreshData, 
        create, 
        isCreating, 
        createError,
        remove,
        isDeleting: isRemoving,
        update,
        isUpdating,
        updateError,
     } = useInstalaciones();

    const [showEdit, setShowEdit] = useState(false);
    const [showCreate, setShowCreate] = useState(false);
    const [instalacionEdit, setInstalacionEdit] = useState(null);

    if (isLoading) return <Loader message="Cargando sedes..." />;
    if (error) return <p>Error al cargar instalaciones</p>;
    if (!instalaciones || instalaciones.length === 0) return <p>No hay instalaciones registradas</p>;

    const handle = (id, name, deleteFunc) => {
        if (window.confirm(`¿Seguro que deseas eliminar ${name}?`)) {
            eventToast(`${name} eliminado correctamente`, "error");
            deleteFunc(id);
        }
    }

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            <PageTitle title="Pinca | Sedes" />
            <div className="flex items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    <img src={Pincalogo} className="w-20" alt="Logo" />
                    <h5 className="text-xl font-bold text-gray-800 mb-2 flex items-center">
                        GESTOR PINCA - PINTURAS INDUSTRIALES DEL CARIBE S.A.S
                    </h5>
                </div>
                <button
                    onClick={() => setShowCreate(true)}
                    className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                    <FaBuilding size={14} /> Nueva Sede
                </button>
            </div>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {instalaciones.map((instalacion, index) => (
                    <div
                    key={`${instalacion.id_instalaciones}-${index}`}
                    className={`bg-white
                                p-5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 
                                border-t-4 border-gray-700 flex flex-col justify-between`}
                    >
                    <div>
                        {/* Nombre + Estado */}
                        <div className="flex items-center justify-between mb-3">
                        <h3 className="text-lg font-extrabold text-gray-900 uppercase truncate">
                            {instalacion.nombre}
                        </h3>
                        </div>

                        <div className="space-y-1 mb-4">
                            <div className="text-sm text-gray-500 flex items-center gap-2">
                                <MdDescription  className="text-gray-400" />
                                <strong>Descripción:</strong> {instalacion.descripcion.toUpperCase()}
                            </div>
                            <div className="text-sm text-gray-500 flex items-center gap-2">
                                <FaCity className="text-gray-400" />
                                <strong>Ciudad:</strong> {instalacion.ciudad.toUpperCase()}
                            </div>
                            <div className="text-sm text-gray-500 flex items-center gap-2">
                                <FaMapMarkerAlt className="text-gray-400" />
                                <strong>Dirección:</strong> {instalacion.direccion}
                            </div>
                            <div className="text-sm text-gray-500 flex items-center gap-2">
                                <FaPhoneAlt className="text-gray-400" />
                                <strong>Teléfono:</strong> {instalacion.telefono}
                            </div>
                        </div>
                    </div>

                    {/* Botones */}
                    <div className="flex gap-3 pt-3 border-t border-gray-100">
                        {/* Eliminar */}
                        <button
                        title="Eliminar instalación"
                        type="button"
                        onClick={(e) => {
                            e.preventDefault();
                            handle(instalacion.id_instalaciones, instalacion.nombre, remove);
                        }}
                        className="flex-1 p-2 cursor-pointer flex items-center justify-center gap-1 
                                    text-red-600 bg-red-50 rounded-lg shadow-lg border border-red-100 
                                    hover:bg-red-100 transition-colors disabled:opacity-50"
                        disabled={isRemoving}
                        >
                        <MdDelete size={18} />
                        Eliminar
                        </button>

                        {/* Editar */}
                        <button
                        title="Editar instalación"
                        type="button"
                        onClick={(e) => {
                            e.preventDefault();
                            setShowEdit(true);
                            setInstalacionEdit(instalacion);
                            eventToast("Sede editada correctamente", "error");
                        }}
                        className="flex-1 cursor-pointer p-2 flex items-center justify-center gap-1 
                                    text-blue-600 bg-blue-50 rounded-lg shadow-lg border border-blue-100 
                                    hover:bg-blue-100 transition-colors"
                        >
                        <FaEdit size={16} />
                        Editar
                        </button>

                        {/* Navegar a bodegas / inventario */}
                        <NavLink
                        to={`/instalaciones/bodegas/${instalacion.id_instalaciones}`}
                        className="flex-1 p-2 cursor-pointer flex items-center justify-center gap-1 
                                    text-gray-600 bg-gray-100 rounded-lg shadow-lg border border-gray-200 
                                    hover:bg-gray-200 transition-colors disabled:opacity-50"
                        title="Ver Bodegas"
                        >
                        <FaWarehouse size={16} />
                        Bodegas
                        </NavLink>
                    </div>
                    </div>
                ))}
                </div>

            {/* Toast */}           
            {toastVisible && (
                <Toast
                    message={toastMessage} 
                    type={toastType}
                    onClose={() => setToastVisible(false)}
                />
            )}

            {/* Modal de edición */}
            {showEdit && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" onClick={() => setShowEdit(false)}>
                    <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative" onClick={e => e.stopPropagation()}>
                        <button
                            className="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl cursor-pointer"
                            onClick={() => setShowEdit(false)}
                        >
                            <IoCloseSharp />
                        </button>
                        <div className="flex items-center space-x-2 mb-4">
                            <FaBuilding className="h-6 w-6 text-blue-500" />
                            <h1 className="text-2xl font-bold">Editar Sede</h1>
                        </div>
                        <form
                            onSubmit={e => {
                                e.preventDefault();
                                update({ id: instalacionEdit.id_instalaciones, data: instalacionEdit }, {
                                    onSuccess: () => {
                                        setShowEdit(false);
                                        refreshData();
                                    }
                                })
                                eventToast("Sede actualizada correctamente", "success");
                            }}
                        >
                            <div className="mb-3">
                                <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                <input
                                    className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                    focus:outline-none focus:ring-2 block w-full p-2"
                                    value={instalacionEdit?.nombre || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, nombre: e.target.value })}
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-sm font-medium text-gray-700">Descripción</label>
                                <input
                                    className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                    focus:outline-none focus:ring-2 block w-full p-2"
                                    value={instalacionEdit?.descripcion || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, descripcion: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-sm font-medium text-gray-700">Ciudad</label>
                                <input
                                    className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                    focus:outline-none focus:ring-2 block w-full p-2"
                                    value={instalacionEdit?.ciudad || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, ciudad: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-sm font-medium text-gray-700">Dirección</label>
                                <input
                                    className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                    focus:outline-none focus:ring-2 block w-full p-2"
                                    value={instalacionEdit?.direccion || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, direccion: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input
                                    className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                                    focus:outline-none focus:ring-2 block w-full p-2"
                                    value={instalacionEdit?.telefono || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, telefono: e.target.value })}
                                />
                            </div>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Empresa</label>
                                <select
                                    name="id_empresa"
                                    value={instalacionEdit?.id_empresa || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, id_empresa: e.target.value })}
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
                                className="w-full disabled:opacity-50 cursor-pointer bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors font-semibold"
                                disabled={isUpdating || !instalacionEdit.id_empresa}
                            >
                                {isUpdating ? "Guardando..." : "Guardar Cambios"}
                            </button>
                            {updateError && (
                                <p className="text-red-500 mt-2">
                                    Error: {updateError.message || "No se pudo actualizar"}
                                </p>
                            )}
                        </form>
                    </div>
                </div>
            )}

            {/* Modal para crear sede */}
           {showCreate && (
            <InstalacionesForm
                onSubmit={() => {
                    setShowCreate(false);
                    refreshData(); 
                }}
                setShowCreate={setShowCreate}
                create={create}
                isCreating={isCreating}
                createError={createError}
                empresas={empresas}
                form={form}
                setForm={setForm}
                handleChange={handleChange}
                eventToast={eventToast}
                refreshData={refreshData}
            />
            )}
        </div>
    );
}
