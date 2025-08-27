import React, { useState } from 'react';
import ProfilePictureOnChat from "@/Components/ProfilePictureOnChat.jsx";
import { Link } from '@inertiajs/react';
import UserMiniModal from "@/Components/ProfileModel";

export default function MineProfileChat({ auth }) {
    const [showModal, setShowModal] = useState(false);
    return (
        <>
            <div className="flex flex-row items-center justify-between px-3 py-2 pt-5">
                <div className="flex items-center w-full pb-3">
                    <div
                        className="flex flex-row min-w-0 items-center justify-between space-x-3.5 cursor-pointer"
                        onClick={() => setShowModal(true)}
                        title="View profile"
                    >
                        <ProfilePictureOnChat user={auth.user} />
                      
                        <div className="flex flex-col flex-1 min-w-0">
                            <span className="text-sm font-medium text-gray-100 truncate">
                                {auth.user.name}
                            </span>
                            <span className="text-xs text-gray-400 truncate">
                                @{auth.user.username}
                            </span>
                        </div>
                    </div>
                </div>

                <div className='px-2'>
                    {/* edit profile */}
                    <Link as="button" href={route('profile.edit')}>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth={1.5}
                            stroke="currentColor"
                            className="w-5 h-5 text-gray-400">
                            <path strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M16.862 3.487a2.25 2.25 0 013.182 3.182l-10.5 10.5a2.25 2.25 0 01-1.061.592l-4.125.825a.75.75 0 01-.902-.902l.825-4.125a2.25 2.25 0 01.592-1.061l10.5-10.5z" />
                            <path strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M19.5 7.5L16.5 4.5" />
                        </svg>
                    </Link>
                </div>

                <div>
                    <Link as="button" method="post" href={route('logout')}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5 text-gray-400">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </Link>
                </div>
            </div>
            {showModal && (
                            <UserMiniModal user={auth.user} onClose={() => setShowModal(false)} />
            )}
        </>
    )
}
