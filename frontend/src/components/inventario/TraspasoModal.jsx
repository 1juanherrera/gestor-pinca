import { useState, useMemo } from 'react';
import { MdClose, MdSwapHorizontalCircle, MdInfoOutline } from 'react-icons/md';
import { CustomSelect } from '../CustomSelect'; 

export const TraspasoModal = ({ item, bodegas, onClose, onConfirm, isSubmitting }) => {
    
    const [destino, setDestino] = useState('');
    const [cantidad, setCantidad] = useState(0);
    const [observacion, setObservacion] = useState('');

    const opcionesDestino = useMemo(() => {
        return bodegas
            .filter(b => b.id_bodegas !== item.bodegas_id)
            .map(b => ({ value: b.id_bodegas, label: b.nombre.toUpperCase() }));
    }, [bodegas, item.bodegas_id]);

    const stockActual = item.cantidad || 0;
    const esInvalido = cantidad <= 0 || cantidad > stockActual || !destino;

    const handleSubmit = (e) => {
        e.preventDefault();
        if (esInvalido) return;
        
        onConfirm({
            item_id: item.id_item_general,
            bodega_origen_id: item.bodega_id,
            bodega_destino_id: destino,
            cantidad: parseFloat(cantidad),
            observacion
        })
    }

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-60">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                {/* Header */}
                <div className="bg-orange-600 text-white p-4 flex justify-between items-center">
                    <h2 className="text-lg font-bold flex items-center gap-2 uppercase">
                        <MdSwapHorizontalCircle size={24} /> Traspaso de Inventario
                    </h2>
                    <button onClick={onClose} className="hover:bg-orange-700 cursor-pointer rounded-full p-1 transition-colors">
                        <MdClose size={24} />
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    {/* Info del Producto */}
                    <div className="bg-orange-50 border border-orange-100 p-3 rounded-lg flex gap-3">
                        <MdInfoOutline className="text-orange-600 mt-1" size={20} />
                        <div>
                            <p className="text-sm font-bold text-gray-800 uppercase">{item.nombre}</p>
                            <p className="text-xs text-gray-600">Stock disponible: <span className="font-bold text-orange-700">{stockActual} unidades</span></p>
                        </div>
                    </div>

                    {/* Destino */}
                    <CustomSelect 
                        label="BODEGA DE DESTINO"
                        options={[{value: '', label: 'SELECCIONE DESTINO...'}, ...opcionesDestino]}
                        value={destino}
                        onChange={(e) => setDestino(e.target.value)}
                        isRequired={true}
                    />

                    {/* Cantidad */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-1 uppercase">Cantidad a Mover</label>
                        <input 
                            type="number" 
                            step="0.01"
                            value={cantidad}
                            onChange={(e) => setCantidad(e.target.value)}
                            onFocus={(e) => e.target.select()}
                            className={`w-full bg-white text-sm px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition shadow-sm
                                ${cantidad > stockActual ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'}`}
                        />
                        {cantidad > stockActual && (
                            <p className="text-red-500 text-[10px] font-bold mt-1 uppercase">No puedes traspasar más de lo que hay en stock</p>
                        )}
                    </div>

                    {/* Observación */}
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-1 uppercase">Observación / Nota</label>
                        <textarea 
                            value={observacion}
                            onChange={(e) => setObservacion(e.target.value)}
                            className="w-full bg-white text-sm px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-20 resize-none"
                            placeholder=""
                        />
                    </div>

                    {/* Botones */}
                    <div className="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button 
                            onClick={handleSubmit}
                            type="submit"
                            disabled={esInvalido || isSubmitting}
                            className={`px-6 py-2 rounded-lg text-white font-bold text-sm shadow-md transition-all transform active:scale-95
                                ${esInvalido || isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-orange-600 hover:bg-orange-700'}`}
                        >
                            {isSubmitting ? 'Procesando...' : 'Confirmar'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};