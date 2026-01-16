import { useState } from 'react';
import { formatoMonedaLocal } from '../utils/formatters';

export const InputMoneda = ({ label, name, value, onChange, className, labelClasses }) => {
    const [isFocused, setIsFocused] = useState(false);

    const internalChange = (e) => {
        let val = e.target.value.replace(/[^0-9.]/g, '');

        if (val.length > 1 && val.startsWith('0') && val[1] !== '.') {
            val = val.substring(1);
        }
        
        onChange({ target: { name, value: val } });
    };

    return (
        <div className="w-full">
            <label className={labelClasses}>{label}</label>
            <input
                type="text"
                name={name}
                // Si tiene el foco, muestra el valor crudo. Si no, formatea.
                value={isFocused ? value : formatoMonedaLocal(value)}
                onChange={internalChange}
                onFocus={(e) => {
                    setIsFocused(true);
                    e.target.select();
                }}
                onBlur={() => setIsFocused(false)}
                className={`${className} font-mono`}
                placeholder="$ 0"
            />
        </div>
    );
};