import { FaFlask } from 'react-icons/fa';
import { MdArrowDropDown, MdOutlineCleaningServices  } from 'react-icons/md';
import { AiFillAppstore } from 'react-icons/ai';

export const ProductSelect = ({ 
    productos, 
    selectedProduct, 
    onProductSelect, 
    onClearSelection, 
    loading,
    compact = false 
}) => {
    if (loading) {
        return (
            <div className="bg-white rounded-lg shadow-sm p-3">
                <div className="animate-pulse">
                    <div className="h-3 bg-gray-200 rounded w-1/4 mb-2"></div>
                    <div className="h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        );
    }

    const productosOnly = productos.filter(item => item.tipo === 'PRODUCTO');
    const insumosOnly = productos.filter(item => item.tipo === 'INSUMO');

    return (
        <div className={`bg-white rounded-lg shadow-sm ${compact ? 'p-4' : 'p-6'}`}>
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                    <FaFlask className="text-blue-500" size={compact ? 16 : 20} />
                    <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold text-gray-800`}>
                        Seleccionar Producto o Insumo
                    </h3>
                </div>
                {selectedProduct && (
                    <button
                        onClick={onClearSelection}
                        className="flex items-center cursor-pointer gap-1 px-2 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors"
                    >
                        <MdOutlineCleaningServices size={18}/>
                        Limpiar
                    </button>
                )}
            </div>

            <div className="relative">
                <select
                    value={selectedProduct ?? ''}
                    onChange={(e) => onProductSelect(e.target.value)}
                    className={`w-full px-3 ${compact ? 'py-2' : 'py-3'} pr-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white text-sm`}
                >
                    <option value="">Selecciona un producto o insumo...</option>
                    
                    {productosOnly.length > 0 && (
                        <optgroup label="PRODUCTOS">
                            {productosOnly.map((producto, i) => (
                                <option key={i} value={producto.id_formulacion}>
                                    {producto.codigo} {producto.nombre} 
                                    ({producto.items?.length || 0} comp.)
                                </option>
                            ))}
                        </optgroup>
                    )}
                    
                    {insumosOnly.length > 0 && (
                        <optgroup label="INSUMOS">
                            {insumosOnly.map(insumo => (
                                <option key={insumo.id} value={insumo.id}>
                                    {insumo.codigo} - {insumo.nombre} 
                                    ({insumo.formulaciones?.length || 0} comp.)
                                </option>
                            ))}
                        </optgroup>
                    )}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <MdArrowDropDown className="h-4 w-4 text-gray-400" />
                </div>
            </div>

            {selectedProduct && (
                <div className="mt-3 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <div className="flex items-center gap-2">
                        {productos.find(p => p.id === parseInt(selectedProduct))?.tipo === 'PRODUCTO' ? (
                            <FaFlask className="text-blue-600" size={12} />
                        ) : (
                            <AiFillAppstore className="text-yellow-600" size={12} />
                        )}
                        <p className="text-xs text-blue-800">
                            ✓ {productos.find(p => p.id === parseInt(selectedProduct))?.tipo} seleccionado: {productos.find(p => p.id === parseInt(selectedProduct))?.nombre}
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
};