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
    const amount = formatter.format(value)

    return (amount.includes(',00')) ? amount.slice(0, (amount.length - 3)) : amount
}

function padNumber(num: number, size: number): string {
    let numStr = num.toString();
    while (numStr.length < size) {
        numStr = "0" + numStr;
    } 
    return numStr;
}

const formatDateTime = (time: string | undefined): string  => {

    if (typeof time === 'undefined') {
        return ''
    }

    let utcTime = ''

    if (time.length === 10) {

        utcTime = time + 'T12:00:00+00:00';

    } else {

        // convert to UTC
        // 2025-01-05 23:11:06 become 2025-01-05T23:11:06+00:00
        utcTime = time.includes('T') ? time : time.replace(' ', 'T') + '+00:00';
    }
    
    const dateObj = new Date(utcTime)
    const result = dateObj.toLocaleString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short'
    })

    return (time.length === 10) ? result.substring(0, 10) : result
}

export { buildMenu, hasAccess, formatCurrency, padNumber, formatDateTime }