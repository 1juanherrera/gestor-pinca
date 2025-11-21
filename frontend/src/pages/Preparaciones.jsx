import { useState, useMemo } from "react";
import { 
    FaPlus, FaSearch, FaFlask, FaClock, FaListUl, 
    FaRegCalendarAlt, FaEdit, FaTrash 
} from "react-icons/fa";

export const Preparaciones = () => {

    // MOCK DATA (estático)
    const preparaciones = useMemo(() => [
        {
            id_preparaciones: 1,
            fecha_creacion: "2025-01-10",
            fecha_inicio: "2025-01-11",
            fecha_fin: "2025-01-12",
            cantidad: 500,
            item_general_id: 12,
            unidad_id: 3,
            items: [
                { item_general_id: 1, cantidad: 200, porcentaje: 40 },
                { item_general_id: 2, cantidad: 300, porcentaje: 60 }
            ]
        },
        {
            id_preparaciones: 2,
            fecha_creacion: "2025-01-15",
            fecha_inicio: "2025-01-16",
            fecha_fin: "2025-01-17",
            cantidad: 800,
            item_general_id: 18,
            unidad_id: 3,
            items: [
                { item_general_id: 4, cantidad: 500, porcentaje: 62.5 },
                { item_general_id: 7, cantidad: 300, porcentaje: 37.5 }
            ]
        }
    ], []);

    const [search, setSearch] = useState("");
    const [fechaDesde, setFechaDesde] = useState("");
    const [fechaHasta, setFechaHasta] = useState("");

    const filtered = useMemo(() => {
        return preparaciones.filter(p => {
            if (search) {
                const s = search.toLowerCase();
                if (String(p.id_preparaciones).includes(s)) return true;
                if (String(p.cantidad).includes(s)) return true;
            }
            if (fechaDesde && p.fecha_creacion < fechaDesde) return false;
            if (fechaHasta && p.fecha_creacion > fechaHasta) return false;
            return true;
        });
    }, [search, fechaDesde, fechaHasta, preparaciones]);

    // métrica: cantidad total producida
    const totalProducido = useMemo(() => {
        return preparaciones.reduce((sum, p) => sum + p.cantidad, 0);
    }, [preparaciones]);

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">

            {/* HEADER */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                <div className="flex items-center gap-2">
                    <FaFlask className="text-purple-600 w-7 h-7" />
                    <h1 className="text-xl font-bold text-gray-800">
                        Preparaciones / Mezclas
                    </h1>
                </div>

                <button className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors mt-4 lg:mt-0">
                    <FaPlus className="w-4 h-4" />
                    Nueva Preparación
                </button>
            </div>

            {/* METRICAS */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Total producido</p>
                        <p className="text-2xl font-bold text-purple-700">{totalProducido} unidades</p>
                    </div>
                    <div className="p-3 bg-purple-500 rounded-lg">
                        <FaFlask className="text-purple-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Preparaciones activas</p>
                        <p className="text-2xl font-bold text-blue-700">{filtered.length}</p>
                    </div>
                    <div className="p-3 bg-blue-500 rounded-lg">
                        <FaListUl className="text-blue-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Rango de fechas</p>
                        <p className="text-2xl font-bold text-green-700">
                            {fechaDesde || fechaHasta ? "Filtrado" : "Completo"}
                        </p>
                    </div>
                    <div className="p-3 bg-green-500 rounded-lg">
                        <FaRegCalendarAlt className="text-green-100" size={28} />
                    </div>
                </div>
            </div>

            {/* FILTROS */}
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div className="relative w-full md:w-1/2">
                        <FaSearch className="absolute left-3 top-3 text-gray-400" />
                        <input
                            className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg"
                            placeholder="Buscar por ID o cantidad"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        <input type="date" value={fechaDesde} onChange={e => setFechaDesde(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg" />

                        <input type="date" value={fechaHasta} onChange={e => setFechaHasta(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg" />
                    </div>

                </div>
            </div>

            {/* TABLA */}
            <div className="bg-white rounded-lg shadow overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600">ID</th>
                            <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600">Fecha creación</th>
                            <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600">Inicio</th>
                            <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600">Fin</th>
                            <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600">Cantidad</th>
                            <th className="px-6 py-3 text-center text-xs font-semibold text-gray-600">Ingredientes</th>
                            <th className="px-6 py-3 text-center text-xs font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>

                    <tbody className="bg-white divide-y divide-gray-200">
                        {filtered.map(p => (
                            <tr key={p.id_preparaciones} className="hover:bg-gray-50">

                                <td className="px-6 py-4 whitespace-nowrap text-sm">{p.id_preparaciones}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{p.fecha_creacion}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{p.fecha_inicio}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{p.fecha_fin}</td>

                                <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-purple-800">
                                    {p.cantidad}
                                </td>

                                {/* INGREDIENTES */}
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div className="flex flex-col items-center gap-1">
                                        {p.items.map((i, idx) => (
                                            <span key={idx} className="text-xs bg-gray-100 px-2 py-1 rounded-md text-gray-700">
                                                Item #{i.item_general_id} — {i.cantidad} ({i.porcentaje}%)
                                            </span>
                                        ))}
                                    </div>
                                </td>

                                <td className="px-6 py-4 text-center">
                                    <div className="flex items-center justify-center gap-2">
                                        <button className="p-2 bg-gray-500 text-white hover:bg-gray-800 rounded-md transition-colors cursor-pointer">
                                            <FaEdit className="w-4 h-4" />
                                        </button>
                                        <button className="p-2 bg-red-500 text-white hover:bg-red-700 rounded-md transition-colors cursor-pointer">
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
