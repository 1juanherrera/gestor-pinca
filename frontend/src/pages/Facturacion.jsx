import { useMemo, useState } from 'react';
import { FaPlus, FaSearch, FaFileInvoice, FaRegFileAlt, FaEdit, FaTrash, FaUsers, FaCalendarAlt } from 'react-icons/fa';
import { formatoPesoColombiano } from '../utils/formatters';
import { useFacturas } from '../hooks/useFacturas';
import { FacturaForm } from '../components/facturas/FacturaForm';

export const Facturacion = () => {

    const { 
        data: facturas, 
        // refreshData, 
        // createFactura, 
        // isCreating, 
        // createError, 
        // updateFactura, 
        // isUpdating, 
        // updateError, 
        // removeFactura, 
        // isDeleting, 
        // deleteError 
        } = useFacturas();

    const [showModal, setShowModal] = useState(false);
    const [search, setSearch] = useState('');
    const [filterEstado, setFilterEstado] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    const filtered = useMemo(() => {
        return facturas.filter(f => {
            if (filterEstado && f.estado !== filterEstado) return false;
            if (search) {
                const s = search.toLowerCase();
                const inNumber = (f.numero || '').toLowerCase().includes(s);
                const inCliente = (`Cliente #${f.cliente_id}`).toLowerCase().includes(s);
                return inNumber || inCliente || (String(f.total) || '').includes(s);
            }
            if (fechaDesde && f.fecha_emision < fechaDesde) return false;
            if (fechaHasta && f.fecha_emision > fechaHasta) return false;
            return true;
        });
    }, [facturas, filterEstado, search, fechaDesde, fechaHasta]);

    const totals = useMemo(() => {
        const total = facturas.reduce((s, f) => s + Number(f.total || 0), 0);
        const pendientes = facturas.filter(f => f.estado === 'pendiente').reduce((s, f) => s + Number(f.total || 0), 0);
        const pagadas = facturas.filter(f => f.estado === 'pagada').reduce((s, f) => s + Number(f.total || 0), 0);
        return { total, pendientes, pagadas };
    }, [facturas]);

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            {/* Header */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-center gap-2">
                    <FaUsers className="text-blue-600 w-6 h-6" />
                    <h1 className="text-xl font-bold text-gray-800">
                        Gestión de Facturas
                    </h1>
                </div>
                <div className="flex flex-col sm:flex-row gap-3 mb-4">
                    <button 
                        onClick={() => setShowModal(true)}
                        className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <FaPlus className="w-4 h-4" />
                        Generar Factura
                    </button>
                </div>
            </div>

            {/* Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Total Facturado</p>
                        <p className="text-2xl font-bold text-green-700">{formatoPesoColombiano(totals.total)}</p>
                    </div>
                    <div className="p-3 bg-green-500 rounded-lg">
                        <FaRegFileAlt className="text-green-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Pendientes</p>
                        <p className="text-2xl font-bold text-amber-600">{formatoPesoColombiano(totals.pendientes)}</p>
                    </div>
                    <div className="p-3 bg-amber-500 rounded-lg">
                        <FaCalendarAlt className="text-amber-100" size={28} />
                    </div>
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-gray-500">Pagadas</p>
                        <p className="text-2xl font-bold text-blue-600">{formatoPesoColombiano(totals.pagadas)}</p>
                    </div>
                    <div className="p-3 bg-blue-500 rounded-lg">
                        <FaUsers className="text-blue-100" size={28} />
                    </div>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div className="flex items-center gap-3 w-full md:w-1/2">
                        <div className="relative w-full">
                            <FaSearch className="absolute left-3 top-3 text-gray-400" />
                            <input className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg" placeholder="Buscar por número, cliente o monto" value={search} onChange={e => setSearch(e.target.value)} />
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <select value={filterEstado} onChange={e => setFilterEstado(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagada">Pagada</option>
                            <option value="anulada">Anulada</option>
                        </select>
                        <input type="date" value={fechaDesde} onChange={e => setFechaDesde(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg" />
                        <input type="date" value={fechaHasta} onChange={e => setFechaHasta(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg" />
                    </div>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white rounded-lg shadow overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha emisión</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Impuestos</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retención</th>
                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {filtered.map(f => (
                            <tr key={f.id_facturas} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-700">{f.id_facturas}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{f.numero}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">Cliente #{f.cliente_id}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{f.fecha_emision}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-left text-gray-900">
                                    {formatoPesoColombiano(f.total)}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span className={`inline-flex leading-5 uppercase shadow-sm w-28 items-center justify-center px-4 py-2 rounded-full text-xs font-medium
                                        ${f.estado.toLowerCase() == 'pagada' ? 'bg-green-100 text-green-800 border-green-200' : f.estado.toLowerCase() == 'pendiente' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-red-100 text-red-800 border-red-200'}`}>
                                        {f.estado}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{formatoPesoColombiano(f.impuestos)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{formatoPesoColombiano(f.retencion)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div className="flex items-center justify-center gap-2">
                                        <button className="p-2 bg-gray-500 text-white hover:bg-gray-800 rounded-md transition-colors cursor-pointer"><FaEdit className="w-4 h-4" /></button>
                                        <button className="p-2 bg-red-500 text-white hover:bg-red-700 rounded-md transition-colors cursor-pointer"><FaTrash className="w-4 h-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Create Invoice modal (visual only) */}
            {showModal && (
                <FacturaForm setShowModal={setShowModal} />
            )}
        </div>
    );
};