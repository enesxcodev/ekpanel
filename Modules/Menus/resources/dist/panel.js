//#region Modules/Menus/resources/assets/js/workspace.js
function e({ notify: e }) {
	let t = document.querySelector("[data-menu-workspace]");
	if (!t || t.dataset.initialized) return;
	t.dataset.initialized = "true";
	let n = JSON.parse(t.querySelector("[data-menu-configuration]").textContent), r = (e) => t.querySelector(e), i = r("[data-menu-selector]"), a = r("[data-menu-form]"), o = r("[data-item-form]"), s = r("[data-item-editor]"), c = r("[data-menu-tree]"), l = r("[data-menu-error]"), u = new Map([...t.querySelectorAll("[data-menu-icon]")].map((e) => [e.dataset.menuIcon, e])), d = n.menus, f = null, p = null, m = !1, h = !1, g = !1, _ = !1, v = null, y = /* @__PURE__ */ new Set(), b = {
		group: "Bağlantısız grup",
		url: "Özel URL",
		route: "Site rotası",
		anchor: "Sayfa içi bağlantı",
		content: "İçerik"
	};
	function x(e, t = "", n = "") {
		let r = document.createElement(e);
		return r.className = t, r.textContent = n, r;
	}
	function S(e) {
		return (u.get(e) ?? u.get("file")).content.cloneNode(!0);
	}
	function C(e) {
		r("[data-menu-live]").textContent = e;
	}
	function w(e) {
		let t = e?.querySelector("[data-form-errors]") ?? l;
		t.replaceChildren(), t.hidden = !0, e?.querySelectorAll("[aria-invalid]").forEach((e) => {
			e.removeAttribute("aria-invalid"), e.classList.remove("is-invalid");
		});
	}
	function T(e, t) {
		w(t);
		let n = t?.querySelector("[data-form-errors]") ?? l, r = e.fields ? Object.values(e.fields).flat() : [e.message];
		[...new Set(r)].forEach((e) => n.append(x("div", "", e))), n.hidden = !1, Object.keys(e.fields ?? {}).forEach((e) => {
			[...t?.elements ?? []].filter((t) => t.name === e && !t.disabled).forEach((e) => {
				e.classList.add("is-invalid"), e.setAttribute("aria-invalid", "true");
			});
		}), n.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		});
	}
	async function E(e, t = "GET", n) {
		let r = await fetch(e, {
			method: t,
			credentials: "same-origin",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
				"X-Requested-With": "XMLHttpRequest",
				"X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]")?.content ?? ""
			},
			...n === void 0 ? {} : { body: JSON.stringify(n) }
		}), i = await r.json().catch(() => ({}));
		if (!r.ok) {
			let e = r.status === 419 ? "Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin." : r.status === 403 ? "Bu işlem için yetkiniz yok." : r.status === 404 ? "Menü veya öğe bulunamadı. Listeyi yenileyin." : "İşlem tamamlanamadı. Yeniden deneyin.", t = Error(i.message ?? e);
			throw t.fields = i.errors, t;
		}
		return i;
	}
	async function D(e, n = null) {
		if (_) return;
		_ = !0, w(n), t.classList.add("is-busy"), t.setAttribute("aria-busy", "true");
		let i = document.activeElement;
		t.inert = !0;
		try {
			await e();
		} catch (e) {
			T(e instanceof TypeError ? /* @__PURE__ */ Error("Sunucuya ulaşılamadı. Bağlantınızı kontrol edip yeniden deneyin.") : e, e.recorded ? null : n);
		} finally {
			t.inert = !1, _ = !1, t.classList.remove("is-busy"), t.removeAttribute("aria-busy"), v ? (r(v)?.focus({ preventScroll: !0 }), v = null) : i?.isConnected && i.checkVisibility?.() && i.focus({ preventScroll: !0 });
		}
	}
	function O() {
		return !(h || g) || window.confirm("Kaydedilmemiş değişiklikler var. Değişikliklerden vazgeçilsin mi?");
	}
	function k(e = null) {
		if (e) {
			let t = d.findIndex((t) => t.id === e.id);
			t < 0 ? d.push(e) : d[t] = e;
		}
		i.replaceChildren(new Option(d.length ? "Menü seçin" : "Henüz menü yok", "")), d.forEach((e) => i.add(new Option(`${e.name} · ${e.key}`, String(e.id)))), i.value = String(f?.menu.id ?? "");
	}
	function A(e) {
		return x("span", `menu-status${e ? " is-active" : ""}`, e ? "Aktif" : "Pasif");
	}
	function j() {
		r("[data-menu-builder]").hidden = !f, r("[data-menu-empty]").hidden = !!f, r("[data-menu-key]").textContent = f?.menu.key ?? "—", r("[data-menu-count]").textContent = String(f?.items.length ?? 0), r("[data-menu-status]").replaceChildren(f ? A(f.menu.is_active) : document.createTextNode("—")), r("[data-menu-edit]") && (r("[data-menu-edit]").hidden = !f);
	}
	async function M(e, t = null) {
		let n = await E(e.workspace_url);
		f = n, p = null, h = !1, g = !1, a.hidden = !0, s && (s.hidden = !0), k(n.menu), j(), I(), t && f.items.some((e) => e.id === t) && L(t, !1);
		let r = new URL(window.location.href);
		r.searchParams.set("menu", n.menu.id), r.searchParams.delete("new"), r.searchParams.delete("item"), window.history.replaceState(null, "", r), C(`${n.menu.name} yüklendi. ${n.items.length} öğe.`);
	}
	async function N(e, t = null) {
		h = !1, g = !1;
		try {
			await M(e, t);
		} catch {
			let e = /* @__PURE__ */ Error("İşlem kaydedildi ancak güncel görünüm yüklenemedi. Menü seçicisinden menüyü tekrar seçerek yenileyin.");
			throw e.recorded = !0, e;
		}
	}
	function P(e) {
		let t = /* @__PURE__ */ new Set([e]), n = [e];
		for (; n.length;) {
			let e = n.pop();
			f.items.filter((n) => n.parent_id === e && !t.has(n.id)).forEach((e) => {
				t.add(e.id), n.push(e.id);
			});
		}
		return t;
	}
	function F(e) {
		let t = /* @__PURE__ */ new Set([e.id]), n = 0, r = e.parent_id;
		for (; r !== null && !t.has(r);) t.add(r), n++, r = f.items.find((e) => e.id === r)?.parent_id ?? null;
		return n;
	}
	function I() {
		c.replaceChildren(), r("[data-menu-tree-empty]").hidden = f.items.length > 0;
		let e = /* @__PURE__ */ new Set();
		f.items.forEach((t) => {
			if (t.parent_id !== null && (y.has(t.parent_id) || e.has(t.parent_id))) {
				e.add(t.id);
				return;
			}
			let r = x("div", `menu-row${t.id === p ? " is-selected" : ""}`);
			if (r.dataset.id = t.id, r.setAttribute("role", "listitem"), r.style.setProperty("--menu-depth", F(t)), n.permissions.update) {
				let e = x("button", "menu-drag-handle", "⠿");
				e.type = "button", e.dataset.move = t.id, e.setAttribute("aria-label", `${t.label} öğesini taşı`), e.setAttribute("aria-describedby", "menu-drag-help"), r.append(e);
			}
			r.append(S(t.icon || (t.target_type === "group" ? "folder" : t.target_type === "content" ? "file" : "link")));
			let i = x("div", "menu-row-label"), a = x(n.permissions.update ? "button" : "span", "menu-row-title btn-reset", t.label);
			n.permissions.update && (a.type = "button", a.dataset.edit = t.id);
			let o = n.sources.find((e) => e.key === t.source);
			i.append(a, x("span", "badge bg-secondary-lt", t.target_type === "content" ? o?.label ?? "İçerik" : b[t.target_type]));
			let s = x("span", `menu-row-target${t.target.error ? " text-danger" : ""}`, t.target.error ?? t.target.url ?? (t.dynamic_children ? "Otomatik alt içerikler" : ""));
			if (i.append(s), r.append(i, A(t.is_active)), f.items.some((e) => e.parent_id === t.id)) {
				let e = x("button", "btn btn-sm btn-ghost-secondary", y.has(t.id) ? "›" : "⌄");
				e.type = "button", e.dataset.collapse = t.id, e.setAttribute("aria-expanded", String(!y.has(t.id))), e.setAttribute("aria-label", `${t.label} alt öğelerini aç/kapat`), r.append(e);
			}
			if (n.permissions.update) {
				let e = x("button", "btn btn-sm btn-outline-secondary", "✎");
				e.type = "button", e.dataset.edit = t.id, e.setAttribute("aria-label", `${t.label} öğesini düzenle`);
				let n = x("button", "btn btn-sm btn-outline-danger");
				n.type = "button", n.dataset.delete = t.id, n.setAttribute("aria-label", `${t.label} öğesini sil`), n.append(S("trash")), r.append(e, n);
			}
			c.append(r);
		});
	}
	function L(e, i = !0) {
		if (!o || p === e && h || !O()) return;
		let c = f.items.find((t) => t.id === e);
		if (!c) return;
		p = e, h = !1, g = !1, a.hidden = !0, o.reset(), w(o);
		for (let e of [
			"label",
			"icon",
			"target_type",
			"source",
			"sort_order"
		]) o.elements.namedItem(e).value = c[e] ?? "";
		for (let e of ["is_active", "new_tab"]) o.elements.namedItem(e).checked = !!c[e];
		t.querySelectorAll("[data-item-target]").forEach((e) => {
			e.hidden = e.dataset.itemTarget !== c.target_type, e.disabled = e.hidden;
		});
		let l = r(`[data-item-target="${c.target_type}"]`);
		for (let e of [
			"url",
			"route_name",
			"anchor",
			"source_id"
		]) {
			let t = l.querySelector(`[name="${e}"]`);
			t && e !== "source_id" && (t.value = c[e] ?? "");
		}
		let u = l.querySelector("[name=\"route_parameters_json\"]");
		if (u && (u.value = c.route_parameters && Object.keys(c.route_parameters).length ? JSON.stringify(c.route_parameters, null, 2) : ""), c.target_type === "content") {
			let e = n.sources.find((e) => e.key === c.source), t = l.querySelector("[name=\"source_id\"]");
			t.replaceChildren(new Option("İçerik seçin", "")), (e?.options ?? []).forEach((e) => t.add(new Option(e.label, String(e.value)))), [...t.options].some((e) => e.value === String(c.source_id)) || t.add(new Option(`${c.source_id} (kayıt kullanılamıyor)`, String(c.source_id))), t.value = String(c.source_id), l.querySelector("[data-item-source-label]").textContent = `Bağlantı (${e?.label ?? "Kullanılamayan kaynak"})`, l.querySelector("[data-item-source-status]").textContent = c.target.error ?? "Seçili içeriğin ziyaretçi bağlantısı kullanılır.";
			let r = l.querySelector("[name=\"dynamic_children\"]");
			l.querySelector("[data-item-dynamic]").hidden = !e?.supports_children, r.disabled = !e?.supports_children, r.checked = !!c.dynamic_children;
		}
		let d = P(e), m = o.elements.namedItem("parent_id");
		m.replaceChildren(new Option("Ana öğe (en üst seviye)", "")), f.items.filter((e) => !d.has(e.id) && !e.dynamic_children).forEach((e) => m.add(new Option(`${"— ".repeat(F(e))}${e.label}`, String(e.id)))), m.value = String(c.parent_id ?? ""), r("[data-item-unsaved]").hidden = !0, s.hidden = !1, I(), i && (s.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		}), o.elements.namedItem("label").focus({ preventScroll: !0 }));
	}
	function R(e) {
		if (O()) {
			if (m = e, a.reset(), w(a), g = !1, h = !1, s && (s.hidden = !0), r("[data-menu-form-title]").textContent = e ? "Yeni menü oluştur" : "Menü bilgileri", !e && f) {
				for (let e of [
					"name",
					"key",
					"icon"
				]) a.elements.namedItem(e).value = f.menu[e] ?? "";
				a.elements.namedItem("is_active").checked = f.menu.is_active;
			}
			r("[data-menu-delete]") && (r("[data-menu-delete]").hidden = e), a.hidden = !1, e ? (r("[data-menu-builder]").hidden = !0, r("[data-menu-empty]").hidden = !0) : j(), _ && (v = "#menu-name"), a.elements.namedItem("name").focus();
		}
	}
	i.addEventListener("change", () => {
		let e = d.find((e) => e.id === Number(i.value));
		!e || !O() ? i.value = String(f?.menu.id ?? "") : D(async () => {
			try {
				await M(e);
			} catch (e) {
				throw i.value = String(f?.menu.id ?? ""), e;
			}
		});
	}), r("[data-menu-new]")?.addEventListener("click", () => R(!0)), r("[data-menu-edit]")?.addEventListener("click", () => R(!1)), r("[data-menu-cancel]")?.addEventListener("click", () => {
		O() && (g = !1, a.hidden = !0, j());
	}), a.addEventListener("input", () => {
		g = !0;
	}), a.addEventListener("submit", (t) => {
		t.preventDefault();
		let r = Object.fromEntries(new FormData(a));
		r.is_active = a.elements.namedItem("is_active").checked, D(async () => {
			let t = await E(m ? n.storeUrl : f.urls.update, m ? "POST" : "PUT", r);
			a.hidden = !0, k(t.menu), e({
				type: "success",
				message: t.message
			}), await N(t.menu);
		}, a);
	}), r("[data-menu-delete]")?.addEventListener("click", () => {
		f && window.confirm("Bu menü ve tüm öğeleri silinsin mi?") && D(async () => {
			let t = await E(f.urls.delete, "DELETE");
			if (d = d.filter((e) => e.id !== f.menu.id), f = null, p = null, g = !1, h = !1, a.hidden = !0, s && (s.hidden = !0), d.length) await M(d[0]);
			else {
				k(), j();
				let e = new URL(window.location.href);
				e.searchParams.delete("menu"), window.history.replaceState(null, "", e);
			}
			e({
				type: "success",
				message: t.message
			});
		});
	}), t.querySelectorAll("[data-source-search]").forEach((e) => e.addEventListener("input", () => {
		let t = e.closest("form"), n = e.value.toLocaleLowerCase("tr").trim(), r = [...t.querySelectorAll(".menu-source-option")];
		r.forEach((e) => {
			e.hidden = !e.textContent.toLocaleLowerCase("tr").includes(n), e.hidden && (e.querySelector("input").checked = !1);
		}), t.querySelector("[data-source-no-results]").hidden = r.some((e) => !e.hidden);
	})), t.querySelectorAll("[data-menu-add]").forEach((t) => t.addEventListener("submit", (n) => {
		if (n.preventDefault(), !f || !O()) return;
		let r = Object.fromEntries(new FormData(t));
		r.target_type = t.dataset.type, r.is_active = t.elements.namedItem("is_active")?.checked ?? !0, r.new_tab = !1, r.sort_order = Math.min(65535, Math.max(-1, ...f.items.filter((e) => e.parent_id === null).map((e) => e.sort_order)) + 1), r.target_type === "content" && (r.source = t.dataset.source, r.label = t.querySelector("input[name=\"source_id\"]:checked")?.dataset.label ?? ""), D(async () => {
			let n = await E(f.urls.add, "POST", r);
			t.reset(), t.querySelectorAll(".menu-source-option").forEach((e) => {
				e.hidden = !1;
			}), t.querySelector("[data-source-no-results]") && (t.querySelector("[data-source-no-results]").hidden = !0), e({
				type: "success",
				message: n.message
			}), await N(f.menu, n.id);
		}, t);
	})), o?.addEventListener("input", () => {
		h = !0, r("[data-item-unsaved]").hidden = !1;
	}), o?.addEventListener("change", () => {
		h = !0, r("[data-item-unsaved]").hidden = !1;
	}), o?.addEventListener("submit", (t) => {
		t.preventDefault();
		let n = f.items.find((e) => e.id === p), r = Object.fromEntries(new FormData(o));
		r.is_active = o.elements.namedItem("is_active").checked, r.new_tab = o.elements.namedItem("new_tab").checked, r.dynamic_children = o.querySelector("[name=\"dynamic_children\"]").checked && !o.querySelector("[name=\"dynamic_children\"]").disabled, r.parent_id = r.parent_id ? Number(r.parent_id) : null, r.sort_order = Number(r.sort_order), D(async () => {
			e({
				type: "success",
				message: (await E(n.update_url, "PUT", r)).message
			}), await N(f.menu, n.id);
		}, o);
	}), r("[data-item-cancel]")?.addEventListener("click", () => {
		O() && (h = !1, L(p, !1));
	});
	function z(t) {
		let n = f.items.find((e) => e.id === t);
		n && O() && window.confirm(`“${n.label}” öğesi silinsin mi?`) && D(async () => {
			e({
				type: "success",
				message: (await E(n.delete_url, "DELETE")).message
			}), await N(f.menu, p === t ? null : p);
		});
	}
	r("[data-item-delete]")?.addEventListener("click", () => z(p)), c.addEventListener("click", (e) => {
		let t = e.target.closest("[data-edit]"), n = e.target.closest("[data-delete]"), r = e.target.closest("[data-collapse]");
		if (!_ && (t && L(Number(t.dataset.edit)), n && z(Number(n.dataset.delete)), r)) {
			let e = Number(r.dataset.collapse);
			y.has(e) ? y.delete(e) : y.add(e), I();
		}
	}), r("[data-menu-expand]").addEventListener("click", () => {
		y.clear(), I();
	}), r("[data-menu-collapse]").addEventListener("click", () => {
		f.items.forEach((e) => y.add(e.id)), I();
	});
	function B(e, t, n) {
		if (_ || !O()) return;
		let r = P(e), i = f.items.find((e) => e.id === t);
		if (!i || r.has(t) || n === "inside" && i.dynamic_children) return;
		let a = structuredClone(f.items), o = f.items.filter((e) => r.has(e.id)), s = P(t), c = f.items.filter((e) => !r.has(e.id)), l = c.findIndex((e) => e.id === t);
		if (n !== "before") for (l++; l < c.length && s.has(c[l].id);) l++;
		o[0].parent_id = n === "inside" ? t : i.parent_id, f.items = [
			...c.slice(0, l),
			...o,
			...c.slice(l)
		];
		let u = /* @__PURE__ */ new Map();
		f.items.forEach((e) => {
			let t = e.parent_id;
			e.sort_order = u.get(t) ?? 0, u.set(t, e.sort_order + 1);
		}), y.delete(t), I(), D(async () => {
			try {
				await E(f.urls.reorder, "PUT", { items: f.items.map((e) => ({
					id: e.id,
					parent_id: e.parent_id,
					sort_order: e.sort_order
				})) });
			} catch (e) {
				throw f.items = a, I(), e;
			}
			await N(f.menu, p), C("Yeni sıra ve üst öğe ilişkisi kaydedildi."), v = `[data-move="${e}"]`;
		});
	}
	c.addEventListener("keydown", (e) => {
		let t = e.target.closest("[data-move]");
		if (!t || ![
			"ArrowUp",
			"ArrowDown",
			"ArrowRight",
			"ArrowLeft"
		].includes(e.key) || _) return;
		e.preventDefault();
		let n = Number(t.dataset.move), r = f.items.find((e) => e.id === n), i = f.items.filter((e) => e.parent_id === r.parent_id), a = i.findIndex((e) => e.id === n);
		e.key === "ArrowUp" && a > 0 && B(n, i[a - 1].id, "before"), e.key === "ArrowDown" && a < i.length - 1 && B(n, i[a + 1].id, "after"), e.key === "ArrowRight" && a > 0 && B(n, i[a - 1].id, "inside"), e.key === "ArrowLeft" && r.parent_id !== null && B(n, r.parent_id, "after");
	});
	let V = null, H = null;
	function U() {
		c.querySelectorAll(".drop-before, .drop-after, .drop-inside").forEach((e) => e.classList.remove("drop-before", "drop-after", "drop-inside"));
	}
	function W() {
		if (!V?.started) return;
		U(), V.target = null;
		let e = document.elementFromPoint(V.x, V.y)?.closest(".menu-row");
		if (!e || !c.contains(e)) return;
		let t = Number(e.dataset.id);
		if (P(V.id).has(t)) return;
		let n = e.getBoundingClientRect(), r = (V.y - n.top) / n.height, i = r < .27 ? "before" : r > .73 ? "after" : "inside";
		if (i === "inside" && f.items.find((e) => e.id === t).dynamic_children) return;
		V.target = {
			id: t,
			placement: i
		};
		let a = e;
		if (i === "after") {
			let n = P(t), r = [...c.querySelectorAll(".menu-row")].filter((e) => n.has(Number(e.dataset.id)));
			a = r[r.length - 1] ?? e;
		}
		a.classList.add(`drop-${i}`);
	}
	function G() {
		if (!V?.started) return;
		let e = V.y < 90 ? -9 : V.y > window.innerHeight - 90 ? 9 : 0;
		e && (window.scrollBy(0, e), W()), H = requestAnimationFrame(G);
	}
	function K(e, t = !1) {
		if (!V || e.pointerId !== void 0 && e.pointerId !== V.pointerId) return;
		let n = V;
		V = null, H && cancelAnimationFrame(H), n.preview?.remove(), n.handle.classList.remove("is-dragging"), c.querySelectorAll(".is-dragging").forEach((e) => e.classList.remove("is-dragging")), U(), n.handle.hasPointerCapture(n.pointerId) && n.handle.releasePointerCapture(n.pointerId), !t && n.target && B(n.id, n.target.id, n.target.placement);
	}
	c.addEventListener("pointerdown", (e) => {
		let t = e.target.closest("[data-move]");
		t && !_ && e.button === 0 && (e.preventDefault(), t.focus({ preventScroll: !0 }), V = {
			id: Number(t.dataset.move),
			pointerId: e.pointerId,
			startX: e.clientX,
			startY: e.clientY,
			x: e.clientX,
			y: e.clientY,
			handle: t,
			started: !1
		}, t.setPointerCapture(e.pointerId));
	}), c.addEventListener("pointermove", (e) => {
		V && V.pointerId === e.pointerId && (V.x = e.clientX, V.y = e.clientY, !(!V.started && Math.hypot(V.x - V.startX, V.y - V.startY) < 6) && (V.started || (V.started = !0, V.preview = x("div", "menu-drag-preview", f.items.find((e) => e.id === V.id).label), document.body.append(V.preview), V.handle.closest(".menu-row").classList.add("is-dragging"), H = requestAnimationFrame(G)), V.preview.style.left = `${V.x + 12}px`, V.preview.style.top = `${V.y + 12}px`, W()));
	}), c.addEventListener("pointerup", (e) => K(e)), c.addEventListener("pointercancel", (e) => K(e, !0)), c.addEventListener("lostpointercapture", (e) => K(e, !0)), document.addEventListener("keydown", (e) => {
		e.key === "Escape" && V && K({}, !0);
	}), window.addEventListener("beforeunload", (e) => {
		(h || g) && (e.preventDefault(), e.returnValue = "");
	}), i.replaceChildren(new Option(d.length ? "Menü seçin" : "Henüz menü yok", "")), d.forEach((e) => i.add(new Option(`${e.name} · ${e.key}`, String(e.id)))), j();
	let q = d.find((e) => e.id === n.initialMenu) ?? d[0];
	q ? D(async () => {
		await M(q, n.initialItem), n.newMenu && n.permissions.create && R(!0);
	}) : n.permissions.create && R(!0);
}
//#endregion
//#region Modules/Menus/resources/assets/js/menus.js
function t() {
	document.querySelectorAll("[data-menu-item-form]").forEach((e) => {
		let t = e.querySelector("[data-menu-target]"), n = e.querySelector("[data-menu-source]"), r = e.querySelector("[data-menu-source-id]"), i = e.querySelector("[data-menu-label]"), a = e.querySelector("[data-menu-dynamic]"), o = e.querySelector("[data-menu-dynamic-fields]"), s = e.querySelector("[data-menu-source-status]");
		if (!t || !n || !r || !i || !a || !o || !s) return;
		let c = new Map([...r.querySelectorAll("[data-menu-source-group]")].map((e) => [e.dataset.menuSourceGroup, e.cloneNode(!0)])), l = n.value, u = () => {
			let e = t.value === "content" && n.selectedOptions[0]?.dataset.supportsChildren === "1";
			o.hidden = !e, a.disabled = !e, e || (a.checked = !1);
		}, d = () => {
			e.querySelectorAll("[data-menu-fields]").forEach((e) => {
				let n = e.dataset.menuFields.split(" ").includes(t.value);
				e.hidden = !n, e.querySelectorAll("[data-menu-required]").forEach((e) => {
					let r = e.dataset.menuRequired.split(" ").filter(Boolean);
					e.required = n && (r.length === 0 || r.includes(t.value));
				});
			}), u();
		}, f = () => {
			let e = n.value === l ? r.value : "", t = document.createElement("option");
			t.value = "", t.textContent = "İçerik seçin", r.replaceChildren(t);
			let i = c.get(n.value);
			i && r.append(i.cloneNode(!0)), r.value = e, r.selectedIndex < 0 && (r.value = "");
			let a = r.options.length > 1;
			s.textContent = n.value === "" ? "Önce bir içerik kaynağı seçin." : a ? "Bağlantı adresi bulunmayan içerikler yayınlanan menüde bağlantıya dönüşmez." : "Bu kaynakta seçilebilir kayıt yok. Kaynak modülün etkinliğini ve içeriklerini kontrol edin.", l = n.value, u();
		};
		t.addEventListener("change", d), n.addEventListener("change", f), r.addEventListener("change", () => {
			i.value.trim() === "" && r.value !== "" && (i.value = r.selectedOptions[0]?.textContent.trim() ?? "");
		}), f(), d();
	});
}
//#endregion
//#region Modules/Menus/resources/assets/js/panel.js
function n(n) {
	e(n), t();
}
//#endregion
export { n as default };
