// import { ProductSelect } from '../components/formulaciones/ProductSelect';
// import { FormulacionesTable } from '../components/formulaciones/FormulacionesTable';
// import { CostCalculator } from '../components/formulaciones/CostCalculator';
// import { ProductSpecificationsTable } from '../components/formulaciones/ProductSpecificationsTable';
import { FaFlask, FaChartPie, FaSpinner, FaCube } from 'react-icons/fa';
import { MdScience } from 'react-icons/md';
// import { formatoCantidad } from '../utils/formatters';
// import { CostProductsTable } from '../components/formulaciones/CostProductsTable';     
import { useFormulaciones } from '../hooks/useFormulaciones';

export const Formulaciones = () => {
  const { 
    data, 
    isLoading, 
    error, 
    productos, 
    insumos ,
    refreshData,
    selectedProduct,
    handleProductSelect,
    handleClearSelection,
  } = useFormulaciones();

  data.forEach(item => console.log(item.nombre));

  if (isLoading) return <div>Cargando...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div className="ml-65 p-4 bg-gray-100 min-h-screen">
      {/* Header Compacto */}
      <div className="mb-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <FaFlask className="text-blue-500" size={20} />
            <div>
              <h1 className="text-xl font-bold text-gray-800">
                Formulaciones y Calculadora
              </h1>
            </div>
          </div>
          <button
            onClick={refreshData}
            disabled={isLoading}
            className="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors"
          >
            {isLoading ? (
              <>
                <FaSpinner className="animate-spin" size={14} />
                Cargando...
              </>
            ) : (
              <>
                <MdScience size={14} />
                Actualizar
              </>
            )}
          </button>
        </div>
      </div>

      {/* Estadísticas Compactas */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
        <div className="bg-white rounded-lg shadow-sm p-3">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-medium text-gray-600">Productos</p>
              <p className="text-lg font-bold text-blue-600">
                {productos.length}
              </p>
            </div>
            <div className="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
              <FaFlask className="h-4 w-4 text-blue-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-3">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-medium text-gray-600">Insumos</p>
              <p className="text-lg font-bold text-yellow-600">
                {insumos.length}
              </p>
            </div>
            <div className="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
              <FaCube className="h-4 w-4 text-yellow-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-3">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-medium text-gray-600">Total Items</p>
              <p className="text-lg font-bold text-purple-600">
                {data.length}
              </p>
            </div>
            <div className="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
              <MdScience className="h-4 w-4 text-purple-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-3">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-medium text-gray-600">Componentes</p>
                <p className="text-lg font-bold text-emerald-600">
                  0
                </p>
            </div>
            <div className="h-8 w-8 bg-emerald-100 rounded-full flex items-center justify-center">
              <MdScience className="h-4 w-4 text-emerald-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-3">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-medium text-gray-600">Costo Total</p>
                <p className="text-lg font-bold text-green-600">
                  0,00
                </p>
            </div>
            <div className="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
              <FaChartPie className="h-4 w-4 text-green-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Selector de producto compacto */}
      <div className="mb-4">
        <ProductSelect
          productos={productos}
          selectedProduct={selectedProduct}
          onProductSelect={handleProductSelect}
          onClearSelection={handleClearSelection}
          loading={isLoading}
          compact={true} // AGREGADO: Prop para modo compacto
        />
      </div>
    </div>
  )
}

//             {/* Selector de producto compacto */}
//             <div className="mb-4">
//                 <ProductSelect
//                     productos={productos}
//                     selectedProduct={selectedProduct}
//                     onProductSelect={handleProductSelect}
//                     onClearSelection={handleClearSelection}
//                     loading={loading}
//                     compact={true} // AGREGADO: Prop para modo compacto
//                 />
//             </div>

//             {/* Mensaje de error compacto */}
//             {(error || calculatorError) && (
//                 <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
//                     <div className="flex items-center justify-between">
//                         <p className="text-sm text-red-800">
//                             Error: {error || calculatorError}
//                         </p>
//                         <button
//                             onClick={() => {
//                                 refreshData();
//                                 clearCalculatorError();
//                             }}
//                             className="px-2 py-1 text-xs bg-red-100 text-red-800 rounded hover:bg-red-200 transition-colors"
//                         >
//                             Reintentar
//                         </button>
//                     </div>
//                 </div>
//             )}

//             {/* Layout integrado - Todo en una vista */}
//             <div className="grid grid-cols-1 2xl:grid-cols-3 gap-4">
//                 {/* Calculadora de Costos - Compacta */}
//                 <div className="2xl:col-span-1">
//                     <CostCalculator
//                         selectedProductData={selectedProductData}
//                         onCalculate={handleCalculateCosts}
//                         onSave={handleSaveCosts}
//                         loading={calculatorLoading}
//                         onCalculationChange={handleCalculationChange}
//                         compact={true} // AGREGADO: Prop para modo compacto
//                     />
//                 </div>

//                 {/* Tabla de formulaciones - Ocupa más espacio */}
//                 <div className="2xl:col-span-2">
//                     <FormulacionesTable
//                         formulaciones={formulaciones}
//                         selectedProductData={selectedProductData}
//                         totalCantidad={totalCantidad}
//                         totalCosto={totalCosto}
//                         calculationResult={calculationResult}
//                         compact={true} // AGREGADO: Prop para modo compacto
//                     />
//                 </div>
//             </div>

//             {/* Especificaciones y Costos */}
//             <div className="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
//                 {/* Desglose de Costos */}
//                 <div>
//                     <CostProductsTable
//                         selectedProductData={selectedProductData}
//                         calculationResult={calculationResult}
//                         compact={true}
//                     />
//                 </div>
//                 {/* Especificaciones Técnicas */}
//                 <div>
//                     <ProductSpecificationsTable
//                         selectedProductData={selectedProductData}
//                     />
//                 </div>
            
//             </div>
//         </div>
//     );
// };