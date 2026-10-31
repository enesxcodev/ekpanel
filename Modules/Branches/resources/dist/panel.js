//#region Modules/Branches/resources/assets/js/panel.js
function e(e) {
	document.querySelectorAll("[data-branches-language]").forEach((e) => {
		e.addEventListener("change", () => {
			let t = new URL(window.location.href);
			t.searchParams.set("lang", e.value), window.location.assign(t.toString());
		});
	}), document.querySelectorAll("[data-branches-form]").forEach((e) => {
		let t = !1;
		e.addEventListener("input", (e) => {
			e.isTrusted && e.target.name !== "lang_id" && (t = !0);
		}), e.addEventListener("change", (e) => {
			e.isTrusted && e.target.name !== "lang_id" && (t = !0);
		}), e.querySelectorAll("[data-branches-language-link]").forEach((e) => {
			e.addEventListener("click", (e) => {
				t && !window.confirm("Kaydedilmemiş değişiklikleriniz var. Dil değiştirilirse bu değişiklikler kaybolacak. Devam edilsin mi?") && e.preventDefault();
			});
		});
		let n = window.$, r = [...e.querySelectorAll("[data-location-select]")], i = (t) => Object.fromEntries((t.dataset.parents || "").split(",").filter(Boolean).map((t) => [t, e.querySelector(`[name="${t}"]`)?.value || ""])), a = (e) => Object.values(i(e)).every(Boolean), o = (e) => {
			e.replaceChildren(new Option(e.dataset.placeholder, "")), n(e).val(null).trigger("change.select2");
		}, s = (e) => {
			let t = !e.dataset.parents || a(e);
			e.disabled = !t, n(e).prop("disabled", !t).trigger("change.select2");
		}, c = async (t) => {
			if (!a(t)) return;
			let n = new URLSearchParams({
				entity: t.dataset.entity,
				...i(t)
			}), r = await fetch(`${e.dataset.locationOptionsUrl}?${n}`);
			if (!r.ok) return;
			let c = await r.json();
			o(t), c.items.forEach((e) => t.add(new Option(e.name, e.id))), s(t);
		};
		r.forEach((e) => {
			n(e).select2({
				width: "100%",
				placeholder: e.dataset.placeholder,
				allowClear: !0
			}), s(e);
		}), r.forEach((e, t) => {
			n(e).on("change.branches", () => {
				r.slice(t + 1).forEach((e) => {
					o(e), s(e);
				});
				let e = r[t + 1];
				e !== void 0 && c(e);
			});
		});
	});
}
//#endregion
export { e as default };
