//#region Modules/Pages/resources/assets/js/panel.js
function e(e, t, n, r = () => JSON.stringify([...new FormData(e)].map(([e, t]) => [e, t instanceof File ? `${t.name}:${t.size}:${t.type}` : t]))) {
	if (!e || t.length === 0) return;
	let i = !1;
	e.addEventListener("input", () => {
		i = !0;
	}), e.addEventListener("change", () => {
		i = !0;
	});
	let a = r();
	t.forEach((e) => e.addEventListener("click", (e) => {
		(i || r() !== a) && !n("Kaydedilmemiş değişiklikleriniz var. Dil değiştirmek istiyor musunuz?") && e.preventDefault();
	}));
}
function t() {
	let t = [...document.querySelectorAll("[data-pages-language-link]")], n = t[0]?.closest("form");
	e(n, t, (e) => window.confirm(e));
}
//#endregion
export { t as default, e as guardLanguageSwitches };
