import { AccessMenu, AccessModule, NavItem } from "@/types";

const buildMenu = (menus?: AccessMenu[]): NavItem[] => {
    const menuList: NavItem[] = []
    
    if (typeof menus !== 'undefined') {
        menus.forEach((menu) => {
            menuList.push({
                title: menu.name,
                href: route(menu.route_name),
                icon: menu.icon
            })
        })
    }

    return menuList
}

const hasAccess = (moduleName: string, modules?: AccessModule[]): boolean => {
    if (typeof modules === 'undefined') {
        return false
    }

    const filteredModules = modules.filter((module) => {
        if (module.name === moduleName) {
            return module
        }
    })

    return filteredModules.length > 0
}

const formatCurrency = (value: number): string => {
    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });
    return formatter.format(value)
}

function padNumber(num: number, size: number): string {
    let numStr = num.toString();
    while (numStr.length < size) {
        numStr = "0" + numStr;
    } 
    return numStr;
}

export { buildMenu, hasAccess, formatCurrency, padNumber }