// resources/js/utils/iconMap.ts

import {
    ArrowRight, BadgeInfo, BarChart3, Bed, Book, Calculator, Camera,
    CheckCircle2, ChevronLeft, CircleDollarSign, Dumbbell, ExternalLink,
    Eye, EyeOff, FileIcon, FileText, Flower, Grip, Heart, Home, Image,
    LayoutGrid, Leaf, Link, List, MessageCircle, MousePointerClick, Package,
    PackageSearch, Plus, PlusCircle, ReceiptText, Scissors, Search, Send,
    Share2, ShoppingBag, ShoppingCart, Smartphone, Smile, SquareCheckBig,
    Star, StarOff, Store, StoreIcon, Trash, Trash2, UploadCloud, User,
    UserRound, UsersRound, UserStar, X, Zap,
} from 'lucide-vue-next'
import type { Component } from 'vue'

export type IconComponent = Component
export type IconMap = Record<string, IconComponent>

export const iconMap: IconMap = {
    ArrowRight, BadgeInfo, BarChart3, Bed, Book, Calculator, Camera,
    CheckCircle2, ChevronLeft, CircleDollarSign, Dumbbell, ExternalLink,
    Eye, EyeOff, FileIcon, FileText, Flower, Grip, Heart, Home, Image,
    LayoutGrid, Leaf, Link, List, MessageCircle, MousePointerClick, Package,
    PackageSearch, Plus, PlusCircle, ReceiptText, Scissors, Search, Send,
    Share2, ShoppingBag, ShoppingCart, Smartphone, Smile, SquareCheckBig,
    Star, StarOff, Store, StoreIcon, Trash, Trash2, UploadCloud, User,
    UserRound, UsersRound, UserStar, X, Zap,
}

export function getIcon(name: string): IconComponent {
    return iconMap[name] ?? iconMap['Store'] 
}

export default iconMap
