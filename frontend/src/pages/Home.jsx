import { FaBoxOpen, FaMapMarkerAlt, FaCity, FaPhoneAlt, FaBuilding, FaEdit } from "react-icons/fa";
import { useState } from "react";
import { useInstalaciones } from "../hooks/useinstalaciones";
import { IoCloseSharp } from "react-icons/io5";
import Pincalogo from "../assets/pincalogo.png";
import { NavLink } from "react-router-dom";

export const Home = () => {

    const { data: instalaciones, isLoading, error } = useInstalaciones();
    const [showEdit, setShowEdit] = useState(false);
    const [instalacionEdit, setInstalacionEdit] = useState(null);

    if (isLoading) return <p>Cargando instalaciones...</p>;
    if (error) return <p>Error al cargar instalaciones</p>;
    if (!instalaciones || instalaciones.length === 0) return <p>No hay instalaciones registradas</p>;

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            <div className="flex items-center gap-2">
                <img src={Pincalogo} className="w-20" alt="Logo" />
                <div>
                    <h5 className="text-xl font-bold text-gray-800 mb-2 flex items-center">
                        GESTOR PINCA - PINTURAS INDUSTRIALES DEL CARIBE S.A.S
                    </h5>
                </div>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {instalaciones.map((instalacion) => (
                    <NavLink
                        to={`/instalaciones/${instalacion.id_instalaciones}/bodega`}
                        key={instalacion.id_instalaciones}
                        className="bg-white cursor-pointer rounded-lg shadow-md p-4 border border-gray-200 scale-hover relative"
                        type="button"
                    >
                        <h3 className="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                            <FaBuilding className="text-blue-400" />
                            {instalacion.nombre}
                            <button
                                className="ml-auto p-1 rounded hover:bg-blue-100 transition-colors absolute top-2 right-2"
                                title="Editar instalación"
                                type="button"
                                onClick={e => { e.stopPropagation(); setShowEdit(true); setInstalacionEdit(instalacion); }}
                            >
                                <FaEdit className="text-blue-500" />
                            </button>
                        </h3>
                        <p className="text-sm text-gray-600 mb-2 flex items-center gap-2">
                            <FaBoxOpen className="text-gray-400" />
                            {instalacion.descripcion}
                        </p>
                        <div className="text-xs text-gray-500 mb-1 flex items-center gap-2">
                            <FaCity className="text-purple-400" />
                            <strong>Ciudad:</strong> {instalacion.ciudad.toUpperCase()}
                        </div>
                        <div className="text-xs text-gray-500 mb-1 flex items-center gap-2">
                            <FaMapMarkerAlt className="text-red-400" />
                            <strong>Dirección:</strong> {instalacion.direccion}
                        </div>
                        <div className="text-xs text-gray-500 mb-1 flex items-center gap-2">
                            <FaPhoneAlt className="text-green-400" />
                            <strong>Teléfono:</strong> {instalacion.telefono}
                        </div>
                    </NavLink>
                ))}
            </div>

            {/* Modal de edición */}
            {showEdit && (
                <div className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
                        <button
                            className="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl cursor-pointer"
                            onClick={() => setShowEdit(false)}
                        >
                            <IoCloseSharp />
                        </button>
                        <h2 className="text-lg font-bold mb-4 text-blue-700 flex items-center gap-2">
                            <FaEdit /> Editar Instalación
                        </h2>
                        <form
                            onSubmit={e => {
                                e.preventDefault();
                                // Aquí iría la lógica para guardar cambios (API call)
                                setShowEdit(false);
                            }}
                        >
                            <div className="mb-3">
                                <label className="block text-xs font-semibold mb-1">Nombre</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.nombre || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, nombre: e.target.value })}
                                    required
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-xs font-semibold mb-1">Descripción</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.descripcion || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, descripcion: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-xs font-semibold mb-1">Ciudad</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.ciudad || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, ciudad: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-xs font-semibold mb-1">Dirección</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.direccion || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, direccion: e.target.value })}
                                />
                            </div>
                            <div className="mb-3">
                                <label className="block text-xs font-semibold mb-1">Teléfono</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.telefono || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, telefono: e.target.value })}
                                />
                            </div>
                            <div className="mb-4">
                                <label className="block text-xs font-semibold mb-1">ID Empresa</label>
                                <input
                                    className="w-full border rounded px-2 py-1"
                                    value={instalacionEdit?.id_empresa || ''}
                                    onChange={e => setInstalacionEdit({ ...instalacionEdit, id_empresa: e.target.value })}
                                />
                            </div>
                            <button
                                type="submit"
                                className="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors font-semibold"
                            >
                                Guardar Cambios
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};