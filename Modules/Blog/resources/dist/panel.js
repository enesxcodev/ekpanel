//#region Modules/Blog/resources/assets/js/panel.js
function e() {
	let e = document.querySelectorAll("[data-blog-language-link]"), t = e[0]?.closest("form");
	if (!t) return;
	let n = !1;
	t.addEventListener("input", () => {
		n = !0;
	}), t.addEventListener("change", () => {
		n = !0;
	});
	let r = () => JSON.stringify([...new FormData(t)].map(([e, t]) => [e, t instanceof File ? `${t.name}:${t.size}:${t.type}` : t])), i = r();
	e.forEach((e) => e.addEventListener("click", (e) => {
		(n || r() !== i) && !window.confirm("Kaydedilmemiş değişiklikleriniz var. Dil değiştirmek istiyor musunuz?") && e.preventDefault();
	}));
}
//#endregion
export { e as default };
