
    const rawSvg: string = `
    <svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <!-- Definimos un círculo como clip -->
    <defs>
        <clipPath id="avatarClip">
        <circle cx="100" cy="100" r="100" />
        </clipPath>
    </defs>

    <!-- Fondo -->
    <circle cx="100" cy="100" r="100" fill="#f0f0f0" />

    <!-- Grupo recortado dentro del círculo -->
    <g clip-path="url(#avatarClip)">
        <!-- Cabeza -->
        <circle cx="100" cy="70" r="40" fill="#c4c4c4" />

        <!-- Busto (bajo la cabeza y recortado en forma circular) -->
        <path d="M30 200c0-40 32-70 70-70s70 30 70 70H30z" fill="#c4c4c4" />
    </g>
    </svg>
    `;

    export const svg_perfil = `data:image/svg+xml;utf8,${encodeURIComponent(rawSvg)}`;
