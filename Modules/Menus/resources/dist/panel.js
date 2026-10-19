//#region Modules/Menus/resources/assets/js/workspace.js
function e({ notify: e, confirm: t }) {
	let n = document.querySelector("[data-menu-workspace]");
	if (!n || n.dataset.initialized) return;
	n.dataset.initialized = "true";
	let r = JSON.parse(n.querySelector("[data-menu-configuration]").textContent), i = (e) => n.querySelector(e), a = i("[data-menu-selector]"), o = i("[data-menu-form]"), s = i("[data-item-form]"), c = i("[data-item-editor]"), l = i("[data-menu-tree]"), u = i("[data-menu-error]"), d = new Map([...n.querySelectorAll("[data-menu-icon]")].map((e) => [e.dataset.menuIcon, e])), f = r.menus, p = null, m = null, h = !1, g = !1, _ = !1, v = !1, y = null, b = /* @__PURE__ */ new Set(), x = {
		group: "Bağlantısız grup",
		url: "Özel URL",
		route: "Site rotası",
		anchor: "Sayfa içi bağlantı",
		content: "İçerik"
	};
	function S(e, t = "", n = "") {
		let r = document.createElement(e);
		return r.className = t, r.textContent = n, r;
	}
	function C(e) {
		return (d.get(e) ?? d.get("file")).content.cloneNode(!0);
	}
	function w(e) {
		i("[data-menu-live]").textContent = e;
	}
	function T(e) {
		let t = e?.querySelector("[data-form-errors]") ?? u;
		t.replaceChildren(), t.hidden = !0, e?.querySelectorAll("[aria-invalid]").forEach((e) => {
			e.removeAttribute("aria-invalid"), e.classList.remove("is-invalid");
		});
	}
	function E(e, t) {
		T(t);
		let n = t?.querySelector("[data-form-errors]") ?? u, r = e.fields ? Object.values(e.fields).flat() : [e.message];
		[...new Set(r)].forEach((e) => n.append(S("div", "", e))), n.hidden = !1, Object.keys(e.fields ?? {}).forEach((e) => {
			[...t?.elements ?? []].filter((t) => t.name === e && !t.disabled).forEach((e) => {
				e.classList.add("is-invalid"), e.setAttribute("aria-invalid", "true");
			});
		}), n.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		});
	}
	async function D(e, t = "GET", n) {
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
	async function O(e, t = null) {
		if (v) return;
		v = !0, T(t), n.classList.add("is-busy"), n.setAttribute("aria-busy", "true");
		let r = document.activeElement;
		n.inert = !0;
		try {
			await e();
		} catch (e) {
			E(e instanceof TypeError ? /* @__PURE__ */ Error("Sunucuya ulaşılamadı. Bağlantınızı kontrol edip yeniden deneyin.") : e, e.recorded ? null : t);
		} finally {
			n.inert = !1, v = !1, n.classList.remove("is-busy"), n.removeAttribute("aria-busy"), y ? (i(y)?.focus({ preventScroll: !0 }), y = null) : r?.isConnected && r.checkVisibility?.() && r.focus({ preventScroll: !0 });
		}
	}
	function k() {
		return !(g || _) || window.confirm("Kaydedilmemiş değişiklikler var. Değişikliklerden vazgeçilsin mi?");
	}
	function A(e = null) {
		if (e) {
			let t = f.findIndex((t) => t.id === e.id);
			t < 0 ? f.push(e) : f[t] = e;
		}
		a.replaceChildren(new Option(f.length ? "Menü seçin" : "Henüz menü yok", "")), f.forEach((e) => a.add(new Option(`${e.name} · ${e.key}`, String(e.id)))), a.value = String(p?.menu.id ?? "");
	}
	function j(e) {
		return S("span", `menu-status${e ? " is-active" : ""}`, e ? "Aktif" : "Pasif");
	}
	function M() {
		i("[data-menu-builder]").hidden = !p, i("[data-menu-empty]").hidden = !!p, i("[data-menu-key]").textContent = p?.menu.key ?? "—", i("[data-menu-count]").textContent = String(p?.items.length ?? 0), i("[data-menu-status]").replaceChildren(p ? j(p.menu.is_active) : document.createTextNode("—")), i("[data-menu-edit]") && (i("[data-menu-edit]").hidden = !p);
	}
	async function N(e, t = null) {
		let n = await D(e.workspace_url);
		p = n, m = null, g = !1, _ = !1, o.hidden = !0, c && (c.hidden = !0), A(n.menu), M(), L(), t && p.items.some((e) => e.id === t) && R(t, !1);
		let r = new URL(window.location.href);
		r.searchParams.set("menu", n.menu.id), r.searchParams.delete("new"), r.searchParams.delete("item"), window.history.replaceState(null, "", r), w(`${n.menu.name} yüklendi. ${n.items.length} öğe.`);
	}
	async function P(e, t = null) {
		g = !1, _ = !1;
		try {
			await N(e, t);
		} catch {
			let e = /* @__PURE__ */ Error("İşlem kaydedildi ancak güncel görünüm yüklenemedi. Menü seçicisinden menüyü tekrar seçerek yenileyin.");
			throw e.recorded = !0, e;
		}
	}
	function F(e) {
		let t = /* @__PURE__ */ new Set([e]), n = [e];
		for (; n.length;) {
			let e = n.pop();
			p.items.filter((n) => n.parent_id === e && !t.has(n.id)).forEach((e) => {
				t.add(e.id), n.push(e.id);
			});
		}
		return t;
	}
	function I(e) {
		let t = /* @__PURE__ */ new Set([e.id]), n = 0, r = e.parent_id;
		for (; r !== null && !t.has(r);) t.add(r), n++, r = p.items.find((e) => e.id === r)?.parent_id ?? null;
		return n;
	}
	function L() {
		l.replaceChildren(), i("[data-menu-tree-empty]").hidden = p.items.length > 0;
		let e = /* @__PURE__ */ new Set();
		p.items.forEach((t) => {
			if (t.parent_id !== null && (b.has(t.parent_id) || e.has(t.parent_id))) {
				e.add(t.id);
				return;
			}
			let n = S("div", `menu-row${t.id === m ? " is-selected" : ""}`);
			if (n.dataset.id = t.id, n.setAttribute("role", "listitem"), n.style.setProperty("--menu-depth", I(t)), r.permissions.update) {
				let e = S("button", "menu-drag-handle", "⠿");
				e.type = "button", e.dataset.move = t.id, e.setAttribute("aria-label", `${t.label} öğesini taşı`), e.setAttribute("aria-describedby", "menu-drag-help"), n.append(e);
			}
			n.append(C(t.icon || (t.target_type === "group" ? "folder" : t.target_type === "content" ? "file" : "link")));
			let i = S("div", "menu-row-label"), a = S(r.permissions.update ? "button" : "span", "menu-row-title btn-reset", t.label);
			r.permissions.update && (a.type = "button", a.dataset.edit = t.id);
			let o = r.sources.find((e) => e.key === t.source);
			i.append(a, S("span", "badge bg-secondary-lt", t.target_type === "content" ? o?.label ?? "İçerik" : x[t.target_type]));
			let s = S("span", `menu-row-target${t.target.error ? " text-danger" : ""}`, t.target.error ?? t.target.url ?? (t.dynamic_children ? "Otomatik alt içerikler" : ""));
			if (i.append(s), n.append(i, j(t.is_active)), p.items.some((e) => e.parent_id === t.id)) {
				let e = S("button", "btn btn-sm btn-ghost-secondary", b.has(t.id) ? "›" : "⌄");
				e.type = "button", e.dataset.collapse = t.id, e.setAttribute("aria-expanded", String(!b.has(t.id))), e.setAttribute("aria-label", `${t.label} alt öğelerini aç/kapat`), n.append(e);
			}
			if (r.permissions.update) {
				let e = S("button", "btn btn-sm btn-outline-secondary", "✎");
				e.type = "button", e.dataset.edit = t.id, e.setAttribute("aria-label", `${t.label} öğesini düzenle`);
				let r = S("button", "btn btn-sm btn-outline-danger");
				r.type = "button", r.dataset.delete = t.id, r.setAttribute("aria-label", `${t.label} öğesini sil`), r.append(C("trash")), n.append(e, r);
			}
			l.append(n);
		});
	}
	function R(e, t = !0) {
		if (!s || m === e && g || !k()) return;
		let a = p.items.find((t) => t.id === e);
		if (!a) return;
		m = e, g = !1, _ = !1, o.hidden = !0, s.reset(), T(s);
		for (let e of [
			"label",
			"icon",
			"target_type",
			"source",
			"sort_order"
		]) s.elements.namedItem(e).value = a[e] ?? "";
		for (let e of ["is_active", "new_tab"]) s.elements.namedItem(e).checked = !!a[e];
		n.querySelectorAll("[data-item-target]").forEach((e) => {
			e.hidden = e.dataset.itemTarget !== a.target_type, e.disabled = e.hidden;
		});
		let l = i(`[data-item-target="${a.target_type}"]`);
		for (let e of [
			"url",
			"route_name",
			"anchor",
			"source_id"
		]) {
			let t = l.querySelector(`[name="${e}"]`);
			t && e !== "source_id" && (t.value = a[e] ?? "");
		}
		let u = l.querySelector("[name=\"route_parameters_json\"]");
		if (u && (u.value = a.route_parameters && Object.keys(a.route_parameters).length ? JSON.stringify(a.route_parameters, null, 2) : ""), a.target_type === "content") {
			let e = r.sources.find((e) => e.key === a.source), t = l.querySelector("[name=\"source_id\"]");
			t.replaceChildren(new Option("İçerik seçin", "")), (e?.options ?? []).forEach((e) => t.add(new Option(e.label, String(e.value)))), [...t.options].some((e) => e.value === String(a.source_id)) || t.add(new Option(`${a.source_id} (kayıt kullanılamıyor)`, String(a.source_id))), t.value = String(a.source_id), l.querySelector("[data-item-source-label]").textContent = `Bağlantı (${e?.label ?? "Kullanılamayan kaynak"})`, l.querySelector("[data-item-source-status]").textContent = a.target.error ?? "Seçili içeriğin ziyaretçi bağlantısı kullanılır.";
			let n = l.querySelector("[name=\"dynamic_children\"]");
			l.querySelector("[data-item-dynamic]").hidden = !e?.supports_children, n.disabled = !e?.supports_children, n.checked = !!a.dynamic_children;
		}
		let d = F(e), f = s.elements.namedItem("parent_id");
		f.replaceChildren(new Option("Ana öğe (en üst seviye)", "")), p.items.filter((e) => !d.has(e.id) && !e.dynamic_children).forEach((e) => f.add(new Option(`${"— ".repeat(I(e))}${e.label}`, String(e.id)))), f.value = String(a.parent_id ?? ""), i("[data-item-unsaved]").hidden = !0, c.hidden = !1, L(), t && (c.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		}), s.elements.namedItem("label").focus({ preventScroll: !0 }));
	}
	function z(e) {
		if (k()) {
			if (h = e, o.reset(), T(o), _ = !1, g = !1, c && (c.hidden = !0), i("[data-menu-form-title]").textContent = e ? "Yeni menü oluştur" : "Menü bilgileri", !e && p) {
				for (let e of [
					"name",
					"key",
					"icon"
				]) o.elements.namedItem(e).value = p.menu[e] ?? "";
				o.elements.namedItem("is_active").checked = p.menu.is_active;
			}
			i("[data-menu-delete]") && (i("[data-menu-delete]").hidden = e), o.hidden = !1, e ? (i("[data-menu-builder]").hidden = !0, i("[data-menu-empty]").hidden = !0) : M(), v && (y = "#menu-name"), o.elements.namedItem("name").focus();
		}
	}
	a.addEventListener("change", () => {
		let e = f.find((e) => e.id === Number(a.value));
		!e || !k() ? a.value = String(p?.menu.id ?? "") : O(async () => {
			try {
				await N(e);
			} catch (e) {
				throw a.value = String(p?.menu.id ?? ""), e;
			}
		});
	}), i("[data-menu-new]")?.addEventListener("click", () => z(!0)), i("[data-menu-edit]")?.addEventListener("click", () => z(!1)), i("[data-menu-cancel]")?.addEventListener("click", () => {
		k() && (_ = !1, o.hidden = !0, M());
	}), o.addEventListener("input", () => {
		_ = !0;
	}), o.addEventListener("submit", (t) => {
		t.preventDefault();
		let n = Object.fromEntries(new FormData(o));
		n.is_active = o.elements.namedItem("is_active").checked, O(async () => {
			let t = await D(h ? r.storeUrl : p.urls.update, h ? "POST" : "PUT", n);
			o.hidden = !0, A(t.menu), e({
				type: "success",
				message: t.message
			}), await P(t.menu);
		}, o);
	}), i("[data-menu-delete]")?.addEventListener("click", async () => {
		p && await t({
			title: "Menüyü sil",
			message: "Bu menü ve tüm öğeleri silinsin mi?",
			confirmLabel: "Sil"
		}) && O(async () => {
			let t = await D(p.urls.delete, "DELETE");
			if (f = f.filter((e) => e.id !== p.menu.id), p = null, m = null, _ = !1, g = !1, o.hidden = !0, c && (c.hidden = !0), f.length) await N(f[0]);
			else {
				A(), M();
				let e = new URL(window.location.href);
				e.searchParams.delete("menu"), window.history.replaceState(null, "", e);
			}
			e({
				type: "success",
				message: t.message
			});
		});
	}), n.querySelectorAll("[data-source-search]").forEach((e) => e.addEventListener("input", () => {
		let t = e.closest("form"), n = e.value.toLocaleLowerCase("tr").trim(), r = [...t.querySelectorAll(".menu-source-option")];
		r.forEach((e) => {
			e.hidden = !e.textContent.toLocaleLowerCase("tr").includes(n), e.hidden && (e.querySelector("input").checked = !1);
		}), t.querySelector("[data-source-no-results]").hidden = r.some((e) => !e.hidden);
	})), n.querySelectorAll("[data-menu-add]").forEach((t) => t.addEventListener("submit", (n) => {
		if (n.preventDefault(), !p || !k()) return;
		let r = Object.fromEntries(new FormData(t));
		r.target_type = t.dataset.type, r.is_active = t.elements.namedItem("is_active")?.checked ?? !0, r.new_tab = !1, r.sort_order = Math.min(65535, Math.max(-1, ...p.items.filter((e) => e.parent_id === null).map((e) => e.sort_order)) + 1), r.target_type === "content" && (r.source = t.dataset.source, r.label = t.querySelector("input[name=\"source_id\"]:checked")?.dataset.label ?? ""), O(async () => {
			let n = await D(p.urls.add, "POST", r);
			t.reset(), t.querySelectorAll(".menu-source-option").forEach((e) => {
				e.hidden = !1;
			}), t.querySelector("[data-source-no-results]") && (t.querySelector("[data-source-no-results]").hidden = !0), e({
				type: "success",
				message: n.message
			}), await P(p.menu, n.id);
		}, t);
	})), s?.addEventListener("input", () => {
		g = !0, i("[data-item-unsaved]").hidden = !1;
	}), s?.addEventListener("change", () => {
		g = !0, i("[data-item-unsaved]").hidden = !1;
	}), s?.addEventListener("submit", (t) => {
		t.preventDefault();
		let n = p.items.find((e) => e.id === m), r = Object.fromEntries(new FormData(s));
		r.is_active = s.elements.namedItem("is_active").checked, r.new_tab = s.elements.namedItem("new_tab").checked, r.dynamic_children = s.querySelector("[name=\"dynamic_children\"]").checked && !s.querySelector("[name=\"dynamic_children\"]").disabled, r.parent_id = r.parent_id ? Number(r.parent_id) : null, r.sort_order = Number(r.sort_order), O(async () => {
			e({
				type: "success",
				message: (await D(n.update_url, "PUT", r)).message
			}), await P(p.menu, n.id);
		}, s);
	}), i("[data-item-cancel]")?.addEventListener("click", () => {
		k() && (g = !1, R(m, !1));
	});
	async function B(n) {
		let r = p.items.find((e) => e.id === n);
		r && k() && await t({
			title: "Menü öğesini sil",
			message: `“${r.label}” öğesi silinsin mi?`,
			confirmLabel: "Sil"
		}) && O(async () => {
			e({
				type: "success",
				message: (await D(r.delete_url, "DELETE")).message
			}), await P(p.menu, m === n ? null : m);
		});
	}
	i("[data-item-delete]")?.addEventListener("click", () => B(m)), l.addEventListener("click", (e) => {
		let t = e.target.closest("[data-edit]"), n = e.target.closest("[data-delete]"), r = e.target.closest("[data-collapse]");
		if (!v && (t && R(Number(t.dataset.edit)), n && B(Number(n.dataset.delete)), r)) {
			let e = Number(r.dataset.collapse);
			b.has(e) ? b.delete(e) : b.add(e), L();
		}
	}), i("[data-menu-expand]").addEventListener("click", () => {
		b.clear(), L();
	}), i("[data-menu-collapse]").addEventListener("click", () => {
		p.items.forEach((e) => b.add(e.id)), L();
	});
	function V(e, t, n) {
		if (v || !k()) return;
		let r = F(e), i = p.items.find((e) => e.id === t);
		if (!i || r.has(t) || n === "inside" && i.dynamic_children) return;
		let a = structuredClone(p.items), o = p.items.filter((e) => r.has(e.id)), s = F(t), c = p.items.filter((e) => !r.has(e.id)), l = c.findIndex((e) => e.id === t);
		if (n !== "before") for (l++; l < c.length && s.has(c[l].id);) l++;
		o[0].parent_id = n === "inside" ? t : i.parent_id, p.items = [
			...c.slice(0, l),
			...o,
			...c.slice(l)
		];
		let u = /* @__PURE__ */ new Map();
		p.items.forEach((e) => {
			let t = e.parent_id;
			e.sort_order = u.get(t) ?? 0, u.set(t, e.sort_order + 1);
		}), b.delete(t), L(), O(async () => {
			try {
				await D(p.urls.reorder, "PUT", { items: p.items.map((e) => ({
					id: e.id,
					parent_id: e.parent_id,
					sort_order: e.sort_order
				})) });
			} catch (e) {
				throw p.items = a, L(), e;
			}
			await P(p.menu, m), w("Yeni sıra ve üst öğe ilişkisi kaydedildi."), y = `[data-move="${e}"]`;
		});
	}
	l.addEventListener("keydown", (e) => {
		let t = e.target.closest("[data-move]");
		if (!t || ![
			"ArrowUp",
			"ArrowDown",
			"ArrowRight",
			"ArrowLeft"
		].includes(e.key) || v) return;
		e.preventDefault();
		let n = Number(t.dataset.move), r = p.items.find((e) => e.id === n), i = p.items.filter((e) => e.parent_id === r.parent_id), a = i.findIndex((e) => e.id === n);
		e.key === "ArrowUp" && a > 0 && V(n, i[a - 1].id, "before"), e.key === "ArrowDown" && a < i.length - 1 && V(n, i[a + 1].id, "after"), e.key === "ArrowRight" && a > 0 && V(n, i[a - 1].id, "inside"), e.key === "ArrowLeft" && r.parent_id !== null && V(n, r.parent_id, "after");
	});
	let H = null, U = null;
	function W() {
		l.querySelectorAll(".drop-before, .drop-after, .drop-inside").forEach((e) => e.classList.remove("drop-before", "drop-after", "drop-inside"));
	}
	function G() {
		if (!H?.started) return;
		W(), H.target = null;
		let e = document.elementFromPoint(H.x, H.y)?.closest(".menu-row");
		if (!e || !l.contains(e)) return;
		let t = Number(e.dataset.id);
		if (F(H.id).has(t)) return;
		let n = e.getBoundingClientRect(), r = (H.y - n.top) / n.height, i = r < .27 ? "before" : r > .73 ? "after" : "inside";
		if (i === "inside" && p.items.find((e) => e.id === t).dynamic_children) return;
		H.target = {
			id: t,
			placement: i
		};
		let a = e;
		if (i === "after") {
			let n = F(t), r = [...l.querySelectorAll(".menu-row")].filter((e) => n.has(Number(e.dataset.id)));
			a = r[r.length - 1] ?? e;
		}
		a.classList.add(`drop-${i}`);
	}
	function K() {
		if (!H?.started) return;
		let e = H.y < 90 ? -9 : H.y > window.innerHeight - 90 ? 9 : 0;
		e && (window.scrollBy(0, e), G()), U = requestAnimationFrame(K);
	}
	function q(e, t = !1) {
		if (!H || e.pointerId !== void 0 && e.pointerId !== H.pointerId) return;
		let n = H;
		H = null, U && cancelAnimationFrame(U), n.preview?.remove(), n.handle.classList.remove("is-dragging"), l.querySelectorAll(".is-dragging").forEach((e) => e.classList.remove("is-dragging")), W(), n.handle.hasPointerCapture(n.pointerId) && n.handle.releasePointerCapture(n.pointerId), !t && n.target && V(n.id, n.target.id, n.target.placement);
	}
	l.addEventListener("pointerdown", (e) => {
		let t = e.target.closest("[data-move]");
		t && !v && e.button === 0 && (e.preventDefault(), t.focus({ preventScroll: !0 }), H = {
			id: Number(t.dataset.move),
			pointerId: e.pointerId,
			startX: e.clientX,
			startY: e.clientY,
			x: e.clientX,
			y: e.clientY,
			handle: t,
			started: !1
		}, t.setPointerCapture(e.pointerId));
	}), l.addEventListener("pointermove", (e) => {
		H && H.pointerId === e.pointerId && (H.x = e.clientX, H.y = e.clientY, !(!H.started && Math.hypot(H.x - H.startX, H.y - H.startY) < 6) && (H.started || (H.started = !0, H.preview = S("div", "menu-drag-preview", p.items.find((e) => e.id === H.id).label), document.body.append(H.preview), H.handle.closest(".menu-row").classList.add("is-dragging"), U = requestAnimationFrame(K)), H.preview.style.left = `${H.x + 12}px`, H.preview.style.top = `${H.y + 12}px`, G()));
	}), l.addEventListener("pointerup", (e) => q(e)), l.addEventListener("pointercancel", (e) => q(e, !0)), l.addEventListener("lostpointercapture", (e) => q(e, !0)), document.addEventListener("keydown", (e) => {
		e.key === "Escape" && H && q({}, !0);
	}), window.addEventListener("beforeunload", (e) => {
		(g || _) && (e.preventDefault(), e.returnValue = "");
	}), a.replaceChildren(new Option(f.length ? "Menü seçin" : "Henüz menü yok", "")), f.forEach((e) => a.add(new Option(`${e.name} · ${e.key}`, String(e.id)))), M();
	let J = f.find((e) => e.id === r.initialMenu) ?? f[0];
	J ? O(async () => {
		await N(J, r.initialItem), r.newMenu && r.permissions.create && z(!0);
	}) : r.permissions.create && z(!0);
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
