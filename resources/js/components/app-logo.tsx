import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md overflow-hidden bg-white">
                <img src="/assets/logo/logo-epoxyndo.png" alt="Logo" className="w-full h-full object-contain p-1" />
            </div>
            <div className="ml-2 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-bold">
                    PT. Epoxyndo
                </span>
                <span className="truncate text-xs text-muted-foreground">
                    Art Lestari
                </span>
            </div>
        </>
    );
}
