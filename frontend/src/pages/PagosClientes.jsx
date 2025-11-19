import { useMemo, useState } from 'react';
import { FaPlus, FaSearch, FaUsers, FaEdit, FaTrash, FaFileInvoiceDollar, FaCalendarAlt, FaFilter } from 'react-icons/fa';
import { formatoPesoColombiano } from '../utils/formatters';

export const PagosClientes = () => {

    const [showModal, setShowModal] = useState(false);
    const [search, setSearch] = useState('');
    const [filterTipo, setFilterTipo] = useState(''); // 'parcial' | 'total' | ''
    const [filterFechaDesde, setFilterFechaDesde] = useState('');
    const [filterFechaHasta, setFilterFechaHasta] = useState('');

    const [pagos] = useState([
        { id: 1, fecha_pago: '2025-11-10', monto: 150000.0, metodo_pago: 'Efectivo', observaciones: 'Pago parcial', clientes_id: 3, facturas_id: 12, tipo_pago: 'parcial' },
        { id: 2, fecha_pago: '2025-11-12', monto: 500000.0, metodo_pago: 'Transferencia', observaciones: 'Pago total factura #11', clientes_id: 2, facturas_id: 11, tipo_pago: 'total' },
        { id: 3, fecha_pago: '2025-11-18', monto: 75000.0, metodo_pago: 'Tarjeta', observaciones: '', clientes_id: 3, facturas_id: null, tipo_pago: 'parcial' }
    ])

    const filtered = useMemo(() => {
        return pagos.filter(p => {
            if (filterTipo && p.tipo_pago !== filterTipo) return false;
            if (search) {
                const s = search.toLowerCase();
                if (!(`${p.monto}`.toLowerCase().includes(s) || (p.observaciones||'').toLowerCase().includes(s) || (p.metodo_pago||'').toLowerCase().includes(s))) return false;
            }
            if (filterFechaDesde && p.fecha_pago < filterFechaDesde) return false;
            if (filterFechaHasta && p.fecha_pago > filterFechaHasta) return false;
            return true;
        });
    }, [pagos, search, filterTipo, filterFechaDesde, filterFechaHasta]);

    const totals = useMemo(() => {
        const total = pagos.reduce((s, p) => s + Number(p.total || 0), 0);
        const totalParcial = pagos.filter(p => p.estado.toLowerCase() === 'pendiente').reduce((s, p) => s + Number(p.monto || 0), 0);
        const totalTotal = pagos.filter(p => p.estado.toLowerCase() === 'pagada').reduce((s, p) => s + Number(p.monto || 0), 0);
        return { total, totalParcial, totalTotal };
    }, [pagos]);

    console.log('Totals:', totals.total, totals.totalParcial, totals.totalTotal);

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            {/* Header */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-center gap-2">
                    <FaUsers className="text-blue-600 w-6 h-6" />
                    <h1 className="text-xl font-bold text-gray-800">
                        Gestión de Pagos
                    </h1>
                </div>
                <div className="flex flex-col sm:flex-row gap-3 mb-4">
                    <button onClick={() => setShowModal(true)}
                        className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <FaPlus className="w-4 h-4" />
                        Nuevo Cliente
                    </button>
                </div>
            </div>

            {/* Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm text-gray-500">Total Pagado</p>
                        <p className="text-2xl font-bold text-green-700">{formatoPesoColombiano(totals.total)}</p>
                    </div>
                    <FaFileInvoiceDollar className="text-green-500" size={28} />
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm text-gray-500">Total Parcial</p>
                        <p className="text-2xl font-bold text-amber-600">{formatoPesoColombiano(totals.totalParcial)}</p>
                    </div>
                    <FaCalendarAlt className="text-amber-500" size={28} />
                </div>

                <div className="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p className="text-sm text-gray-500">Total Total</p>
                        <p className="text-2xl font-bold text-blue-600">{formatoPesoColombiano(totals.totalTotal)}</p>
                    </div>
                    <FaFilter className="text-blue-500" size={28} />
                </div>
            </div>

            {/* Controls */}
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div className="flex items-center gap-2 w-full md:w-1/2">
                        <div className="relative w-full">
                            <FaSearch className="absolute left-3 top-3 text-gray-400" />
                            <input type="text" placeholder="Buscar por monto, método o notas..." value={search} onChange={e => setSearch(e.target.value)} className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg" />
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <select value={filterTipo} onChange={e => setFilterTipo(e.target.value)} className="px-3 py-2 shadow-xs border border-gray-300 rounded-lg">
                            <option value="">Todos los tipos</option>
                            <option value="parcial">Parcial</option>
                            <option value="total">Total</option>
                        </select>

                        <input type="date" value={filterFechaDesde} onChange={e => setFilterFechaDesde(e.target.value)} className="px-3 py-2 shadow-xs border border-gray-300 rounded-lg" />
                        <input type="date" value={filterFechaHasta} onChange={e => setFilterFechaHasta(e.target.value)} className="px-3 py-2 shadow-xs border border-gray-300 rounded-lg" />
                    </div>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white rounded-lg shadow overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {filtered.map(pago => (
                            <tr key={pago.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{pago.fecha_pago}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cliente #{pago.clientes_id}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{formatoPesoColombiano(pago.monto)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{pago.metodo_pago}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pago.tipo_pago === 'total' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-amber-100 text-amber-800 border-amber-200'} 
                                                      shadow-sm block w-24 items-center justify-center px-4 py-2 rounded-full text-xs font-medium`}>
                                        {pago.tipo_pago === 'total' ? 'Total' : 'Parcial'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 truncate max-w-xs text-sm text-gray-600">{pago.observaciones || '-'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{pago.facturas_id ? `#${pago.facturas_id}` : '-'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div className="flex items-center justify-center gap-2">
                                        <button className="p-2 bg-gray-500 text-white rounded-md hover:bg-gray-700"><FaEdit size={12} /></button>
                                        <button className="p-2 bg-red-500 text-white rounded-md hover:bg-red-700"><FaTrash size={12} /></button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Quick Create Modal (visual only) */}
            {showModal && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={() => setShowModal(false)}>
                    <div className="bg-white rounded-lg w-full max-w-2xl p-6" onClick={e => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold">Registrar Pago</h3>
                            <button onClick={() => setShowModal(false)} className="text-gray-500">Cerrar</button>
                        </div>
                        <form>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm text-gray-600">Fecha</label>
                                    <input type="date" className="w-full px-3 py-2 border rounded-lg" />
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-600">Monto</label>
                                    <input type="number" className="w-full px-3 py-2 border rounded-lg" />
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-600">Método</label>
                                    <select className="w-full px-3 py-2 border rounded-lg">
                                        <option>Efectivo</option>
                                        <option>Transferencia</option>
                                        <option>Tarjeta</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-600">Tipo</label>
                                    <select className="w-full px-3 py-2 border rounded-lg">
                                        <option value="parcial">Parcial</option>
                                        <option value="total">Total</option>
                                    </select>
                                </div>
                                <div className="md:col-span-2">
                                    <label className="block text-sm text-gray-600">Observaciones</label>
                                    <input type="text" className="w-full px-3 py-2 border rounded-lg" />
                                </div>
                            </div>

                            <div className="mt-4 flex justify-end gap-2">
                                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border rounded-lg">Cancelar</button>
                                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-lg">Guardar Pago</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};