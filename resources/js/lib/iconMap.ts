// resources/js/utils/iconMap.ts

import * as LucideIcons from 'lucide-vue-next'

export type IconComponent = any
export type IconMap = Record<string, IconComponent>

export const iconMap: IconMap = {
    ...LucideIcons
}

export function getIcon(name: string): IconComponent {
    return iconMap[name] ?? iconMap['Store'] 
}

export default iconMap
