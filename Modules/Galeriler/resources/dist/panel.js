//#region Modules/Galeriler/resources/assets/js/panel.js
function e() {
	document.querySelectorAll("[data-galeriler-form]").forEach((e) => {
		let t = e.querySelector("[data-galeriler-type]"), n = () => {
			let n = t.value === "photo";
			e.querySelector("[data-galeriler-photos]").hidden = !n, e.querySelector("[data-galeriler-video]").hidden = n, e.querySelector("[name=\"image_media_ids\"]").disabled = !n, e.querySelector("[name=\"video_url\"]").disabled = n;
		};
		t.addEventListener("change", n), n();
	});
	let e = document.querySelector("[data-galeriler-grid]");
	if (!e || (document.querySelector("[data-galeriler-select-all]")?.addEventListener("change", (t) => {
		e.querySelectorAll("[name=\"ids[]\"]").forEach((e) => {
			e.checked = t.target.checked;
		});
	}), !e.dataset.galerilerReorder)) return;
	let t = null, n = !1, r = !1, i = () => [...e.querySelectorAll("[data-galeriler-card]")].map((e) => Number(e.dataset.galerilerCard)), a = i();
	e.addEventListener("pointerdown", (e) => {
		r = !!e.target.closest("a, button, input, select, textarea, label, iframe");
	}), e.addEventListener("dragstart", (e) => {
		let o = e.target.closest("[data-galeriler-card]");
		n || r || !o || e.target.closest("a, button, input, select, textarea, label, iframe") ? e.preventDefault() : (t = o, a = i(), e.dataTransfer.effectAllowed = "move", e.dataTransfer.setData("text/plain", t.dataset.galerilerCard));
	}), e.addEventListener("dragover", (e) => {
		t && !n && e.preventDefault();
	}), e.addEventListener("drop", async (r) => {
		r.preventDefault();
		let o = r.target.closest("[data-galeriler-card]");
		if (!t || !o || o === t || n) return;
		let s = [...e.children];
		s.indexOf(t) < s.indexOf(o) ? o.after(t) : o.before(t), t = null, n = !0, e.setAttribute("aria-busy", "true");
		try {
			let t = await fetch(e.dataset.galerilerReorder, {
				method: "PUT",
				credentials: "same-origin",
				headers: {
					"Content-Type": "application/json",
					Accept: "application/json",
					"X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").content
				},
				body: JSON.stringify({
					category_id: Number(e.dataset.galerilerCategory),
					ids: i()
				})
			}), n = await t.json();
			if (!t.ok) throw Error(n.errors ? Object.values(n.errors).flat().join(" ") : n.message || "Sıralama kaydedilemedi.");
			window.Panel.notify({
				type: "success",
				message: n.message
			});
		} catch (t) {
			a.forEach((t) => e.append(e.querySelector("[data-galeriler-card=\"" + t + "\"]"))), window.Panel.notify({
				type: "error",
				message: t.message
			});
		} finally {
			n = !1, e.removeAttribute("aria-busy");
		}
	}), e.addEventListener("dragend", () => {
		t = null;
	});
}
//#endregion
export { e as default };
