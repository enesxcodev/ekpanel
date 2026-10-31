//#region Modules/Projects/resources/assets/js/panel.js
function e(e) {
	document.querySelectorAll("[data-project-form]").forEach((e) => {
		let t = e.querySelector("[name=\"image_media_ids\"]"), n = e.querySelector("[data-project-cover]"), r = n.dataset.selected || n.value, i = new Map([...n.options].filter((e) => e.value).map((e) => [Number(e.value), e.textContent])), a = () => {
			let e = [];
			try {
				e = JSON.parse(t.value || "[]");
			} catch {}
			Array.isArray(e) || (e = []), r = n.value || r, n.replaceChildren(new Option("İlk seçilen görsel", "")), e.forEach((e) => n.add(new Option(i.get(Number(e)) || "Görsel #" + e, String(e)))), n.value = e.some((e) => String(e) === r) ? r : "", r = n.value;
		};
		t.addEventListener("change", a), e.addEventListener("media:selected", (e) => {
			e.target.closest("[data-media-field]")?.querySelector("[name=\"image_media_ids\"]") && (e.detail.files.forEach((e) => i.set(Number(e.id), e.name)), a());
		}), a();
	});
}
//#endregion
export { e as default };
