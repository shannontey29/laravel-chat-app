import React from 'react';
import ProfilePictureOnChat from "@/Components/ProfilePictureOnChat.jsx";

export default function UserMiniModal({ user, onClose }) {
    if (!user) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div className="bg-gray-800 rounded-lg shadow-lg p-6 w-80 relative">
                <button
                    className="absolute top-2 right-2 text-gray-400 hover:text-gray-200"
                    onClick={onClose}
                    aria-label="Close"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div className="flex flex-col items-center">
                    <ProfilePictureOnChat user={user} className="w-20 h-20 mb-4" />
                    <span className="text-lg font-semibold text-gray-100">{user.name}</span>
                    <span className="text-sm text-gray-400 mb-2">@{user.username}</span>
                    <span className="text-sm text-gray-400 mb-2">{user.email}</span>
                    <span className="text-sm text-gray-400 mb-2">
                        last seen at: {user.last_seen_at ? new Date(user.last_seen_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'N/A'}
                    </span>
                    {/* Add more user info here if needed */}
                </div>
            </div>
        </div>
    );
}