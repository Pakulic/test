function manageColorScheme() {
    function getCurrentColorScheme(localStorageColorScheme, systemSettingDark) {
        if (localStorageColorScheme) return localStorageColorScheme;
        return systemSettingDark.matches ? "dark" : "light";
    }

    const switchToDarkButton = document.querySelector(".switch-to-dark");
    const switchToLightButton = document.querySelector(".switch-to-light");
    const localStorageColorScheme = localStorage.getItem("color-scheme");
    const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");

    let currentColorScheme = getCurrentColorScheme(localStorageColorScheme, systemSettingDark);
    document.body.classList.toggle("color-scheme-dark", currentColorScheme === "dark");

    if (switchToDarkButton) {
        switchToDarkButton.addEventListener("click", () => {
            currentColorScheme = "dark";
            applyColorScheme(currentColorScheme);
        });
    }
    
    if (switchToLightButton) {
        switchToLightButton.addEventListener("click", () => {
            currentColorScheme = "light";
            applyColorScheme(currentColorScheme);
        });
    }

    function applyColorScheme(scheme) {
        localStorage.setItem("color-scheme", scheme);
        document.body.classList.toggle("color-scheme-dark", scheme === "dark");
    }
}

manageColorScheme();
