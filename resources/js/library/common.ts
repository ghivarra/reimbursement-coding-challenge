import { AccessMenu, NavItem } from "@/types";

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

export { buildMenu }