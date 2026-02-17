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
    FaEnvelope
} from 'react-icons/fa';
import { ClienteStats } from '../components/cliente/ClienteStats';
import { useClientes } from "../hooks/useClientes";
import { Loader } from '../components/Loader';
import { ClienteForm } from '../components/cliente/ClienteForm';
import { useMemo, useState } from 'react';
import { useToast } from '../hooks/useToast';
import { Toast } from '../components/Toast';

export const Clientes = () => {

    const {
        data: clientes,
        isLoading,
        error,
        refreshData,
        removeCliente,

        createCliente,
        createError,
        isCreating,

        updateCliente,
        updateError,
        isUpdating
    } = useClientes();

    const {
        toastVisible,
        toastMessage,
        toastType,

        eventToast,
        setToastVisible
    } = useToast();

    const [searchTerm, setSearchTerm] = useState('');
    const [editingCliente, setEditingCliente] = useState(null);
    const [showForm, setShowForm] = useState(false); 

    const [form, setForm] = useState({
        nombre_encargado: "",
        nombre_empresa: "",
        numero_documento: "",
        direccion: "",
        telefono: "",
        email: "",
        tipo: "1", 
        estado: "1"
    })

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({
            ...prev,
            [name]: value
        }))
    }

    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value);
    }

    const filteredClientes = useMemo(() => {
        if (!clientes || searchTerm === '') {
            return clientes;
        }

        const lowerCaseSearch = searchTerm.toLowerCase();

        return clientes.filter(cliente => {
            const matchNombre = cliente.nombre_empresa?.toLowerCase().includes(lowerCaseSearch);
            const matchEncargado = cliente.nombre_encargado?.toLowerCase().includes(lowerCaseSearch);
            const matchDocumento = cliente.numero_documento?.toLowerCase().includes(lowerCaseSearch);
            const matchEmail = cliente.email?.toLowerCase().includes(lowerCaseSearch);

            return matchNombre || matchEncargado || matchDocumento || matchEmail;
        });
    }, [clientes, searchTerm])

    const handle = (id, name, deleteFunc) => {
        if (window.confirm(`¿Seguro que deseas eliminar ${name}?`)) {
            eventToast(`${name} eliminado correctamente`, "error");
            deleteFunc(id);
        }
    }

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            {/* Header */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-center gap-2">
                    <FaUsers className="text-blue-600 w-6 h-6" />
                    <h1 className="text-xl font-bold text-gray-800">
                        Gestión de Clientes
                    </h1>
                </div>
                <div className="flex flex-col sm:flex-row gap-3 mb-4">
                    <button 
                        onClick={() => {
                            setShowForm(true);
                            setEditingCliente(null);
                        }}
                        className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <FaPlus className="w-4 h-4" />
                        Nuevo Cliente
                    </button>
                </div>
            </div>
            {/* Estadísticas */}
            <ClienteStats estadisticas={clientes} />

            {/* Filtros y búsqueda */}
            <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    {/* Búsqueda */}
                    <div className="relative flex-1 max-w-md">
                        <FaSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <input
                            type="text"
                            placeholder="Buscar por empresa, encargado, documento..."
                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={searchTerm}
                            onChange={handleSearchChange}
                        />
                    </div>
                </div>
            </div>

            {/* Contenido principal */}
            {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div className="flex justify-between items-center">
                        <p className="text-red-800">{error}</p>
                    </div>
                </div>
            )}

            {isLoading ? (
                <Loader />
            )
                : filteredClientes.length === 0 ? (
                    <div className="bg-white rounded-lg shadow-sm p-12 text-center">
                        <FaUsers className="text-gray-300 mx-auto mb-4 w-16 h-16" />
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">
                            {searchTerm ? 'No se encontraron clientes' : 'No hay clientes registrados'}
                        </h3>
                        <p className="text-gray-600 mb-6">
                            {searchTerm
                                ? 'Intenta con otros términos de búsqueda'
                                : 'Comienza agregando tu primer cliente'}
                        </p>
                        {!searchTerm && (
                            <button 
                                onClick={() => {
                                    setShowForm(true)
                                    setEditingCliente(null);
                                }}
                                className="inline-flex cursor-pointer items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <FaEdit className="w-4 h-4" />
                                Crear primer cliente
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contacto
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Documento
                                        </th>
                                        <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {
                                        filteredClientes.map((cliente, i) => (
                                            <tr key={i} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            {cliente.tipo == "2" ?
                                                            <FaUser className="text-blue-600 w-4 h-4" />
                                                             : 
                                                            <FaBuilding className="text-blue-600 w-4 h-4" />
                                                            }
                                                        </div>
                                                        <div className="ml-4">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {cliente.nombre_empresa}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {cliente.nombre_encargado}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        {cliente.telefono && (
                                                            <div className="flex items-center gap-1 mb-1">
                                                                <FaPhone className="text-gray-400 w-3 h-3" />
                                                                {cliente.telefono}
                                                            </div>
                                                        )}
                                                        {cliente.email && (
                                                            <div className="flex items-center gap-1">
                                                                <FaEnvelope className="text-gray-400 w-3 h-3" />
                                                                {cliente.email}
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {cliente.numero_documento}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex justify-center items-center">
                                                        <span className={`inline-flex ${cliente.estado === "1" ? "bg-green-100 text-green-800 border-green-200" : "bg-red-100 text-red-800 border-red-200"} 
                                                                         shadow-sm border block w-24 items-center justify-center px-4 py-2 rounded-full text-xs font-medium`}>
                                                        {cliente.estado === "1" ? "ACTIVO" : "INACTIVO"}
                                                        </span> 
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <button 
                                                        onClick={() => {
                                                            setForm({
                                                                nombre_encargado: cliente.nombre_encargado,
                                                                nombre_empresa: cliente.nombre_empresa,
                                                                numero_documento: cliente.numero_documento,
                                                                direccion: cliente.direccion,
                                                                telefono: cliente.telefono,
                                                                email: cliente.email,
                                                                tipo: cliente.tipo,
                                                                estado: cliente.estado
                                                            });
                                                            setEditingCliente(cliente);
                                                            setShowForm(true);
                                                        }}
                                                        className="p-2 bg-gray-500 text-white hover:bg-gray-800 rounded-md transition-colors cursor-pointer" title="Editar"><FaEdit className="w-4 h-4" /></button>
                                                        <button 
                                                        onClick={() => handle(cliente.id_clientes, cliente.nombre_empresa, removeCliente)}
                                                        className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer" title="Eliminar"><FaTrash className="w-4 h-4" /></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    }
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

            {/* Toast */}           
            {toastVisible && (
                <Toast 
                    message={toastMessage} 
                    type={toastType}
                    onClose={() => setToastVisible(false)}
                />
            )}

            {/* Modales */}
            {showForm && (
                <ClienteForm
                onSubmit={() => {
                    setShowForm(false);
                    refreshData();
                }}
                refreshData={refreshData}
                setShowForm={setShowForm}
                form={form}
                setForm={setForm}
                handleChange={handleChange}
                createCliente={createCliente}
                createError={createError}
                isCreating={isCreating}
                eventToast={eventToast}
                updateCliente={updateCliente}
                isUpdating={isUpdating}
                updateError={updateError}
                editingCliente={editingCliente}
                setEditingCliente={setEditingCliente}
                />
            )}
            
        </div>
    )
}