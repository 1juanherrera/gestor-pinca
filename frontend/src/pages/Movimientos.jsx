import { useState } from "react";
import {
    FaBoxes,
    FaPlus,
    FaSearch,
    FaArrowDown,
    FaArrowUp,
    FaExchangeAlt,
    FaEdit,
    FaTrash,
    FaWarehouse,
    FaCalendarAlt
} from "react-icons/fa";

export const Movimientos = () => {

    const [search, setSearch] = useState("");
    const [filterTipo, setFilterTipo] = useState("");
    const [fechaDesde, setFechaDesde] = useState("");
    const [fechaHasta, setFechaHasta] = useState("");

    // Datos estáticos simulados
    const movimientos = [
        {
            id: 1,
            codigo: "MP-001",
            nombre: "Pigmento Azul",
            tipo: "Entrada",
            cantidad: 50,
            bodega: "Principal",
            fecha: "2025-01-15",
        },
        {
            id: 2,
            codigo: "PR-010",
            nombre: "Pintura Blanca 1G",
            tipo: "Salida",
            cantidad: 12,
            bodega: "Secundaria",
            fecha: "2025-01-18",
        },
        {
            id: 3,
            codigo: "MP-155",
            nombre: "Resina Base Agua",
            tipo: "Ajuste",
            cantidad: 5,
            bodega: "Principal",
            fecha: "2025-01-20",
        },
    ];

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">

            {/* Header */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4">
                <div className="flex items-center gap-2">
                    <FaBoxes className="text-indigo-600 w-6 h-6" />
                    <h1 className="text-xl font-bold text-gray-800">
                        Movimientos de Inventario
                    </h1>
                </div>

                <button
                    className="cursor-pointer mt-3 lg:mt-0 flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <FaPlus className="w-4 h-4" />
                    Nuevo Movimiento
                </button>
            </div>

            {/* Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                
                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Entradas del Mes</p>
                        <p className="text-2xl font-bold text-green-700">+ 120 uds</p>
                    </div>
                    <div className="p-3 bg-green-500 rounded-lg">
                        <FaArrowDown className="text-green-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Salidas del Mes</p>
                        <p className="text-2xl font-bold text-red-600">- 85 uds</p>
                    </div>
                    <div className="p-3 bg-red-500 rounded-lg">
                        <FaArrowUp className="text-red-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Ajustes Realizados</p>
                        <p className="text-2xl font-bold text-amber-600">7</p>
                    </div>
                    <div className="p-3 bg-amber-500 rounded-lg">
                        <FaExchangeAlt className="text-amber-100" size={28} />
                    </div>
                </div>

            </div>

            {/* Filters */}
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div className="relative w-full md:w-1/2">
                        <FaSearch className="absolute left-3 top-3 text-gray-400" />
                        <input
                            className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Buscar por código o nombre..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        <select
                            value={filterTipo}
                            onChange={e => setFilterTipo(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="ajuste">Ajuste</option>
                        </select>

                        <input
                            type="date"
                            value={fechaDesde}
                            onChange={e => setFechaDesde(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg"
                        />

                        <input
                            type="date"
                            value={fechaHasta}
                            onChange={e => setFechaHasta(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg"
                        />
                    </div>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white rounded-lg shadow overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant.</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bodega</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>

                    <tbody className="bg-white divide-y divide-gray-200">
                        {movimientos.map(m => (
                            <tr key={m.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{m.id}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-medium">{m.codigo}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{m.nombre}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span className={`px-3 py-1 rounded-full text-xs font-semibold shadow-md 
                                        ${m.tipo === "Entrada" ? "bg-green-100 text-green-700" :
                                           m.tipo === "Salida" ? "bg-red-100 text-red-700" :
                                           "bg-amber-100 text-amber-700"}`}>
                                        {m.tipo}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{m.cantidad}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{m.bodega}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{m.fecha}</td>

                                <td className="px-6 py-4 whitespace-nowrap text-center">
                                    <div className="flex items-center justify-center gap-2">
                                        <button className="p-2 bg-gray-500 text-white hover:bg-gray-800 rounded-md">
                                            <FaEdit className="w-4 h-4" />
                                        </button>
                                        <button className="p-2 bg-red-500 text-white hover:bg-red-700 rounded-md">
                                            <FaTrash className="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

        </div>
    );
};
