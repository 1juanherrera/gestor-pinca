

export const FacturaForm = ({ setShowModal }) => {

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50" onClick={() => setShowModal(false)}>
            <div className="bg-white rounded-lg w-full max-w-3xl p-6" onClick={e => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold">Nueva Factura</h3>
                    <button onClick={() => setShowModal(false)} className="text-gray-500">Cerrar</button>
                </div>
                <form>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Número</label>
                            <input className="w-full px-3 py-2 border rounded-lg" placeholder="F-00XX" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Fecha emisión</label>
                            <input type="date" className="w-full px-3 py-2 border rounded-lg" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Cliente</label>
                            <input className="w-full px-3 py-2 border rounded-lg" placeholder="ID o nombre del cliente" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Subtotal</label>
                            <input type="number" className="w-full px-3 py-2 border rounded-lg" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Impuestos</label>
                            <input type="number" className="w-full px-3 py-2 border rounded-lg" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-600">Retención</label>
                            <input type="number" className="w-full px-3 py-2 border rounded-lg" />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-600">Observaciones</label>
                            <input className="w-full px-3 py-2 border rounded-lg" />
                        </div>
                    </div>

                    <div className="mt-4 flex justify-end gap-2">
                        <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border rounded-lg">Cancelar</button>
                        <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-lg">Crear Factura</button>
                    </div>
                </form>
            </div>
        </div>
    )
}