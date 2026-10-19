//#region Modules/Products/resources/assets/js/panel.js
function e() {
	document.querySelectorAll("[data-product-gallery]").forEach((e) => {
		let t = e.querySelector("[name=\"image_media_ids\"]"), n = e.querySelector("[data-product-gallery-order]"), r = new Map([...n.querySelectorAll("[data-media-id]")].map((e) => [Number(e.dataset.mediaId), e.dataset.mediaName])), i = () => {
			try {
				let e = JSON.parse(t.value || "[]");
				return Array.isArray(e) ? e : [];
			} catch {
				return [];
			}
		}, a = () => {
			let e = i();
			n.replaceChildren(), e.forEach((i, a) => {
				let o = document.createElement("li");
				o.className = "list-group-item d-flex align-items-center gap-2";
				let s = document.createElement("span");
				s.className = "me-auto", s.textContent = r.get(Number(i)) || "Görsel #" + i, o.append(s), [["Yukarı", -1], ["Aşağı", 1]].forEach(([n, r]) => {
					let i = document.createElement("button");
					i.type = "button", i.className = "btn btn-sm", i.textContent = n, i.setAttribute("aria-label", s.textContent + ": " + n), i.disabled = a + r < 0 || a + r >= e.length, i.addEventListener("click", () => {
						[e[a], e[a + r]] = [e[a + r], e[a]], t.value = JSON.stringify(e), t.dispatchEvent(new Event("change", { bubbles: !0 }));
					}), o.append(i);
				}), n.append(o);
			});
		};
		t.addEventListener("change", a), e.addEventListener("media:selected", (e) => {
			e.detail.files.forEach((e) => r.set(Number(e.id), e.name)), a();
		}), a();
	});
}
//#endregion
export { e as default };
