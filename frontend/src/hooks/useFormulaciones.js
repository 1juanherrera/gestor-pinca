import { useState } from "react";
import { useApiResource } from "../Connection/getApi";


export const useFormulaciones = () => {
  
  const query = useApiResource('/formulaciones');
  const data = query.data ?? [];

  const productos = data.filter(item => item.tipo === 'PRODUCTO');
  const insumos = data.filter(item => item.tipo === 'INSUMO');

  const [selectedProduct, setSelectedProduct] = useState('');
  const [selectedProductData, setSelectedProductData] = useState(null);
  const [formulaciones, setFormulaciones] = useState([]);
  const [totalCantidad, setTotalCantidad] = useState(0);
  const [totalCosto, setTotalCosto] = useState(0);

  const handleProductSelect = (productId) => {
    setSelectedProduct(productId);

    if (productId) {
      const product = productos.find(p => p.id === parseInt(productId));
      
      if (product) {
        setSelectedProductData(product);
        setFormulaciones(product.formulaciones || []);

        // Calcular totales
        const totalCant = (product.formulaciones || []).reduce((sum, form) => {
          return sum + (parseFloat(form.cantidad) || 0);
        }, 0);

        const totalCost = (product.formulaciones || []).reduce((sum, form) => {
          return sum + (parseFloat(form.costo_total_materia) || 0);
        }, 0);

        setTotalCantidad(totalCant);
        setTotalCosto(totalCost);
      }
    } else {
        setSelectedProductData(null);
        setFormulaciones([]);
        setTotalCantidad(0);
        setTotalCosto(0);
      }
  }

  const handleClearSelection = () => {
    setSelectedProduct('');
    setSelectedProductData(null);
    setFormulaciones([]);
    setTotalCantidad(0);
    setTotalCosto(0);
  };

  return {
    ...query,
    data,
    productos,
    insumos,
    error: query.error,
    selectedProduct,
    selectedProductData,
    formulaciones,
    totalCantidad,
    totalCosto,

    refreshData: query.refetch,
    handleProductSelect,
    handleClearSelection
  };
};