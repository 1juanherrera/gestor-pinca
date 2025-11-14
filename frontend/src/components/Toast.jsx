import { FaCheck, FaRegWindowClose } from "react-icons/fa";
import { IoWarningOutline } from "react-icons/io5";
import { useEffect, useState } from "react";

export const Toast = ({ message, type = "info", onClose }) => {
    const [animate, setAnimate] = useState(false);

    const colors = {
        success: "text-green-600 bg-green-100 border-green-300",
        error: "text-red-600 bg-red-100 border-red-300",
        warning: "text-yellow-600 bg-yellow-100 border-yellow-300",
        info: "text-blue-600 bg-blue-100 border-blue-300",
    };

    const iconColor = colors[type] || "text-gray-600";

    useEffect(() => {
        const entryTimer = setTimeout(() => setAnimate(true), 20);

        const exitTimer = setTimeout(() => {
            setAnimate(false);
            setTimeout(onClose, 300);
        }, 3000);

        return () => {
            clearTimeout(entryTimer);
            clearTimeout(exitTimer);
        };
    }, [onClose]);

    return (
        <div
            className={`
                flex fixed top-2 left-1/2 -translate-x-1/2 z-50
                w-full max-w-xs p-4 border rounded-xl shadow-xl
                transition-all duration-300
                ${colors[type]}
                ${animate ? "opacity-100 translate-y-0" : "opacity-0 -translate-y-4"}
            `}
        >
            {type === "success" && <FaCheck className={`w-5 h-5 ${iconColor}`} />}
            {type === "error" && <FaRegWindowClose className={`w-5 h-5 ${iconColor}`} />}
            {type === "warning" && <IoWarningOutline className={`w-5 h-5 ${iconColor}`} />}
            {type === "info" && <IoWarningOutline className={`w-5 h-5 ${iconColor}`} />}

            <div className="ms-2 text-sm font-semibold text-gray-700">
                {message}
            </div>
        </div>
    );
};
