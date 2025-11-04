import { useState } from "react";
import { useApiResource } from "../Connection/getApi";

export const useFormulaciones = () => {

  const query = useApiResource('/formulaciones');
  const data = query.data ?? [];

  const productos = data.filter(item => item.tipo === 'PRODUCTO');
  const insumos = data.filter(item => item.tipo === 'INSUMO');

  const [selectedProduct, setSelectedProduct] = useState(null);

  const productDetailQuery = useApiResource(selectedProduct ? `/formulaciones/calcular/${selectedProduct}` : 1);
  // const recalculate = useApiMutation(selectedProduct ? `/formulaciones/recalcular` : 'recalculate-disabled');

  const handleProductSelect = (productId) => {
    setSelectedProduct(productId);
  };

  const handleClearSelection = () => {
    setSelectedProduct('');
  };

  return {
    ...query,
    productos,
    insumos,
    selectedProduct,
    setSelectedProduct,
    productDetail: productDetailQuery.data,
    loadingDetail: productDetailQuery.isLoading,
    refreshDetail: productDetailQuery.refetch,
    handleProductSelect,
    handleClearSelection,
  };
};
