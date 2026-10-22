//#region Modules/Branches/resources/assets/js/panel.js
function e() {
	document.querySelectorAll("[data-branches-form]").forEach((e) => {
		let t = window.$, n = [...e.querySelectorAll("[data-location-select]")], r = (t) => Object.fromEntries((t.dataset.parents || "").split(",").filter(Boolean).map((t) => [t, e.querySelector(`[name="${t}"]`)?.value || ""])), i = (e) => Object.values(r(e)).every(Boolean), a = (e) => {
			e.replaceChildren(new Option(e.dataset.placeholder, "")), t(e).val(null).trigger("change.select2");
		}, o = (e) => {
			let n = !e.dataset.parents || i(e);
			e.disabled = !n, t(e).prop("disabled", !n).trigger("change.select2");
		}, s = async (t) => {
			if (!i(t)) return;
			let n = new URLSearchParams({
				entity: t.dataset.entity,
				...r(t)
			}), s = await fetch(`${e.dataset.locationOptionsUrl}?${n}`);
			if (!s.ok) return;
			let c = await s.json();
			a(t), c.items.forEach((e) => t.add(new Option(e.name, e.id))), o(t);
		};
		n.forEach((e) => {
			t(e).select2({
				width: "100%",
				placeholder: e.dataset.placeholder,
				allowClear: !0
			}), o(e);
		}), n.forEach((e, r) => {
			t(e).on("change.branches", () => {
				n.slice(r + 1).forEach((e) => {
					a(e), o(e);
				});
				let e = n[r + 1];
				e !== void 0 && s(e);
			});
		});
	});
}
//#endregion
export { e as default };
