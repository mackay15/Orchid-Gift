import React from 'react';

export default function StarRating({ rating, setRating, readOnly = true }) {
  return (
    <div className="flex items-center gap-1">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          disabled={readOnly}
          onClick={() => !readOnly && setRating && setRating(star)}
          className={`text-lg transition-transform ${
            readOnly ? 'cursor-default' : 'hover:scale-125 cursor-pointer'
          } ${star <= rating ? 'text-amber-400' : 'text-gray-600'}`}
        >
          ★
        </button>
      ))}
    </div>
  );
}
