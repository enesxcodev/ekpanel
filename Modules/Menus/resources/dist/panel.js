//#region Modules/Menus/resources/assets/js/workspace.js
function e(e, t) {
	let n = new URL(e, "http://localhost");
	return Number(t) > 0 ? n.searchParams.set("menu", String(t)) : n.searchParams.delete("menu"), `${n.pathname}${n.search}${n.hash}`;
}
function t({ notify: t, confirm: n }) {
	let r = document.querySelector("[data-menu-workspace]");
	if (!r || r.dataset.initialized) return;
	r.dataset.initialized = "true";
	let i = JSON.parse(r.querySelector("[data-menu-configuration]").textContent), a = (e) => r.querySelector(e), o = a("[data-menu-selector]"), s = a("[data-menu-form]"), c = a("[data-item-form]"), l = a("[data-item-editor]"), u = a("[data-menu-tree]"), d = a("[data-menu-error]"), f = new Map([...r.querySelectorAll("[data-menu-icon]")].map((e) => [e.dataset.menuIcon, e])), p = i.menus, m = null, h = null, g = !1, _ = !1, v = !1, y = !1, b = null, x = /* @__PURE__ */ new Set(), S = {
		group: "Bağlantısız grup",
		url: "Özel URL",
		route: "Site rotası",
		anchor: "Sayfa içi bağlantı",
		content: "İçerik"
	};
	function C(e, t = "", n = "") {
		let r = document.createElement(e);
		return r.className = t, r.textContent = n, r;
	}
	function w(e) {
		return (f.get(e) ?? f.get("file")).content.cloneNode(!0);
	}
	function T(e) {
		a("[data-menu-live]").textContent = e;
	}
	function E(e) {
		let t = e?.querySelector("[data-form-errors]") ?? d;
		t.replaceChildren(), t.hidden = !0, e?.querySelectorAll("[aria-invalid]").forEach((e) => {
			e.removeAttribute("aria-invalid"), e.classList.remove("is-invalid");
		});
	}
	function D(e, t) {
		E(t);
		let n = t?.querySelector("[data-form-errors]") ?? d, r = e.fields ? Object.values(e.fields).flat() : [e.message];
		[...new Set(r)].forEach((e) => n.append(C("div", "", e))), n.hidden = !1, Object.keys(e.fields ?? {}).forEach((e) => {
			[...t?.elements ?? []].filter((t) => t.name === e && !t.disabled).forEach((e) => {
				e.classList.add("is-invalid"), e.setAttribute("aria-invalid", "true");
			});
		}), n.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		});
	}
	async function O(e, t = "GET", n) {
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
	async function k(e, t = null) {
		if (y) return;
		y = !0, E(t), r.classList.add("is-busy"), r.setAttribute("aria-busy", "true");
		let n = document.activeElement;
		r.inert = !0;
		try {
			await e();
		} catch (e) {
			D(e instanceof TypeError ? /* @__PURE__ */ Error("Sunucuya ulaşılamadı. Bağlantınızı kontrol edip yeniden deneyin.") : e, e.recorded ? null : t);
		} finally {
			r.inert = !1, y = !1, r.classList.remove("is-busy"), r.removeAttribute("aria-busy"), b ? (a(b)?.focus({ preventScroll: !0 }), b = null) : n?.isConnected && n.checkVisibility?.() && n.focus({ preventScroll: !0 });
		}
	}
	function A() {
		return !(_ || v) || window.confirm("Kaydedilmemiş değişiklikler var. Değişikliklerden vazgeçilsin mi?");
	}
	function j(e = null) {
		if (e) {
			let t = p.findIndex((t) => t.id === e.id);
			t < 0 ? p.push(e) : p[t] = e;
		}
		o.replaceChildren(new Option(p.length ? "Menü seçin" : "Henüz menü yok", "")), p.forEach((e) => o.add(new Option(`${e.name}${e.translation_exists ? "" : " · Çeviri eklenmedi"} · ${e.key}`, String(e.id)))), o.value = String(m?.menu.id ?? "");
	}
	function M(t) {
		r.querySelectorAll("[data-menu-language]").forEach((n) => {
			n.href = e(n.href, t);
		});
	}
	function N(e) {
		return C("span", `menu-status${e ? " is-active" : ""}`, e ? "Aktif" : "Pasif");
	}
	function P() {
		let e = !!m?.menu.translation_exists;
		a("[data-menu-builder]").hidden = !m || !e, a("[data-menu-empty]").hidden = !!m, a("[data-menu-key]").textContent = m?.menu.key ?? "—", a("[data-menu-count]").textContent = String(m?.items.length ?? 0), a("[data-menu-status]").replaceChildren(m ? N(m.menu.is_active) : document.createTextNode("—")), a("[data-menu-edit]") && (a("[data-menu-edit]").hidden = !m), m && !e ? (a("[data-menu-empty]").hidden = !1, a("[data-menu-empty] .empty-title").textContent = "Bu menünün seçili dilde çevirisi yok", a("[data-menu-empty] .empty-subtitle").textContent = "Menü bilgilerini açıp bu dildeki adını kaydedin. Bu dil için yeni bir öğe ağacı oluşturulur.") : m || (a("[data-menu-empty] .empty-title").textContent = "Bir menü seçin", a("[data-menu-empty] .empty-subtitle").textContent = "Mevcut bir menüyü seçerek başlayın veya yeni menü oluşturun.");
	}
	async function F(e, t = null) {
		let n = await O(e.workspace_url);
		m = n, h = null, _ = !1, v = !1, s.hidden = !0, l && (l.hidden = !0), j(n.menu), P(), z(), t && m.items.some((e) => e.id === t) && B(t, !1);
		let r = new URL(window.location.href);
		r.searchParams.set("menu", n.menu.id), i.language?.code && r.searchParams.set("lang", i.language.code), r.searchParams.delete("new"), r.searchParams.delete("item"), window.history.replaceState(null, "", r), M(n.menu.id), T(`${n.menu.name} yüklendi. ${n.items.length} öğe.`);
	}
	async function I(e, t = null) {
		_ = !1, v = !1;
		try {
			await F(e, t);
		} catch {
			let e = /* @__PURE__ */ Error("İşlem kaydedildi ancak güncel görünüm yüklenemedi. Menü seçicisinden menüyü tekrar seçerek yenileyin.");
			throw e.recorded = !0, e;
		}
	}
	function L(e) {
		let t = /* @__PURE__ */ new Set([e]), n = [e];
		for (; n.length;) {
			let e = n.pop();
			m.items.filter((n) => n.parent_id === e && !t.has(n.id)).forEach((e) => {
				t.add(e.id), n.push(e.id);
			});
		}
		return t;
	}
	function R(e) {
		let t = /* @__PURE__ */ new Set([e.id]), n = 0, r = e.parent_id;
		for (; r !== null && !t.has(r);) t.add(r), n++, r = m.items.find((e) => e.id === r)?.parent_id ?? null;
		return n;
	}
	function z() {
		u.replaceChildren(), a("[data-menu-tree-empty]").hidden = m.items.length > 0;
		let e = /* @__PURE__ */ new Set();
		m.items.forEach((t) => {
			if (t.parent_id !== null && (x.has(t.parent_id) || e.has(t.parent_id))) {
				e.add(t.id);
				return;
			}
			let n = C("div", `menu-row${t.id === h ? " is-selected" : ""}`);
			if (n.dataset.id = t.id, n.setAttribute("role", "listitem"), n.style.setProperty("--menu-depth", R(t)), i.permissions.update) {
				let e = C("button", "menu-drag-handle", "⠿");
				e.type = "button", e.dataset.move = t.id, e.setAttribute("aria-label", `${t.label} öğesini taşı`), e.setAttribute("aria-describedby", "menu-drag-help"), n.append(e);
			}
			n.append(w(t.icon || (t.target_type === "group" ? "folder" : t.target_type === "content" ? "file" : "link")));
			let r = C("div", "menu-row-label"), a = C(i.permissions.update ? "button" : "span", "menu-row-title btn-reset", t.label);
			i.permissions.update && (a.type = "button", a.dataset.edit = t.id);
			let o = i.sources.find((e) => e.key === t.source);
			r.append(a, C("span", "badge bg-secondary-lt", t.target_type === "content" ? o?.label ?? "İçerik" : S[t.target_type]));
			let s = C("span", `menu-row-target${t.target.error ? " text-danger" : ""}`, t.target.error ?? t.target.url ?? (t.dynamic_children ? "Otomatik alt içerikler" : ""));
			if (r.append(s), n.append(r, N(t.is_active)), m.items.some((e) => e.parent_id === t.id)) {
				let e = C("button", "btn btn-sm btn-ghost-secondary", x.has(t.id) ? "›" : "⌄");
				e.type = "button", e.dataset.collapse = t.id, e.setAttribute("aria-expanded", String(!x.has(t.id))), e.setAttribute("aria-label", `${t.label} alt öğelerini aç/kapat`), n.append(e);
			}
			if (i.permissions.update) {
				let e = C("button", "btn btn-sm btn-outline-secondary", "✎");
				e.type = "button", e.dataset.edit = t.id, e.setAttribute("aria-label", `${t.label} öğesini düzenle`);
				let r = C("button", "btn btn-sm btn-outline-danger");
				r.type = "button", r.dataset.delete = t.id, r.setAttribute("aria-label", `${t.label} öğesini sil`), r.append(w("trash")), n.append(e, r);
			}
			u.append(n);
		});
	}
	function B(e, t = !0) {
		if (!c || h === e && _ || !A()) return;
		let n = m.items.find((t) => t.id === e);
		if (!n) return;
		h = e, _ = !1, v = !1, s.hidden = !0, c.reset(), E(c);
		for (let e of [
			"label",
			"icon",
			"target_type",
			"source",
			"sort_order"
		]) c.elements.namedItem(e).value = n[e] ?? "";
		for (let e of ["is_active", "new_tab"]) c.elements.namedItem(e).checked = !!n[e];
		r.querySelectorAll("[data-item-target]").forEach((e) => {
			e.hidden = e.dataset.itemTarget !== n.target_type, e.disabled = e.hidden;
		});
		let o = a(`[data-item-target="${n.target_type}"]`);
		for (let e of [
			"url",
			"route_name",
			"anchor",
			"source_id"
		]) {
			let t = o.querySelector(`[name="${e}"]`);
			t && e !== "source_id" && (t.value = n[e] ?? "");
		}
		let u = o.querySelector("[name=\"route_parameters_json\"]");
		if (u && (u.value = n.route_parameters && Object.keys(n.route_parameters).length ? JSON.stringify(n.route_parameters, null, 2) : ""), n.target_type === "content") {
			let e = i.sources.find((e) => e.key === n.source), t = o.querySelector("[name=\"source_id\"]");
			t.replaceChildren(new Option("İçerik seçin", "")), (e?.options ?? []).forEach((e) => t.add(new Option(e.label, String(e.value)))), [...t.options].some((e) => e.value === String(n.source_id)) || t.add(new Option(`${n.source_id} (kayıt kullanılamıyor)`, String(n.source_id))), t.value = String(n.source_id), o.querySelector("[data-item-source-label]").textContent = `Bağlantı (${e?.label ?? "Kullanılamayan kaynak"})`, o.querySelector("[data-item-source-status]").textContent = n.target.error ?? "Seçili içeriğin ziyaretçi bağlantısı kullanılır.";
			let r = o.querySelector("[name=\"dynamic_children\"]");
			o.querySelector("[data-item-dynamic]").hidden = !e?.supports_children, r.disabled = !e?.supports_children, r.checked = !!n.dynamic_children;
		}
		let d = L(e), f = c.elements.namedItem("parent_id");
		f.replaceChildren(new Option("Ana öğe (en üst seviye)", "")), m.items.filter((e) => !d.has(e.id) && !e.dynamic_children).forEach((e) => f.add(new Option(`${"— ".repeat(R(e))}${e.label}`, String(e.id)))), f.value = String(n.parent_id ?? ""), a("[data-item-unsaved]").hidden = !0, l.hidden = !1, z(), t && (l.scrollIntoView({
			block: "nearest",
			behavior: "auto"
		}), c.elements.namedItem("label").focus({ preventScroll: !0 }));
	}
	function V(e) {
		if (A()) {
			if (g = e, s.reset(), E(s), v = !1, _ = !1, l && (l.hidden = !0), a("[data-menu-form-title]").textContent = e ? "Yeni menü oluştur" : "Menü bilgileri", !e && m) {
				for (let e of [
					"name",
					"key",
					"icon"
				]) s.elements.namedItem(e).value = e === "name" && !m.menu.translation_exists ? "" : m.menu[e] ?? "";
				s.elements.namedItem("is_active").checked = m.menu.is_active;
			}
			a("[data-menu-delete]") && (a("[data-menu-delete]").hidden = e), s.hidden = !1, e ? (a("[data-menu-builder]").hidden = !0, a("[data-menu-empty]").hidden = !0) : P(), y && (b = "#menu-name"), s.elements.namedItem("name").focus();
		}
	}
	o.addEventListener("change", () => {
		let e = p.find((e) => e.id === Number(o.value));
		!e || !A() ? o.value = String(m?.menu.id ?? "") : k(async () => {
			try {
				await F(e);
			} catch (e) {
				throw o.value = String(m?.menu.id ?? ""), e;
			}
		});
	}), a("[data-menu-new]")?.addEventListener("click", () => V(!0)), a("[data-menu-edit]")?.addEventListener("click", () => V(!1)), a("[data-menu-cancel]")?.addEventListener("click", () => {
		A() && (v = !1, s.hidden = !0, P());
	}), s.addEventListener("input", () => {
		v = !0;
	}), s.addEventListener("submit", (e) => {
		e.preventDefault();
		let n = Object.fromEntries(new FormData(s));
		n.lang_id = i.language?.id, n.is_active = s.elements.namedItem("is_active").checked, k(async () => {
			let e = await O(g ? i.storeUrl : m.urls.update, g ? "POST" : "PUT", n);
			s.hidden = !0, j(e.menu), t({
				type: "success",
				message: e.message
			}), await I(e.menu);
		}, s);
	}), a("[data-menu-delete]")?.addEventListener("click", async () => {
		m && await n({
			title: "Menüyü sil",
			message: "Bu menü ve tüm öğeleri silinsin mi?",
			confirmLabel: "Sil"
		}) && k(async () => {
			let e = await O(m.urls.delete, "DELETE");
			if (p = p.filter((e) => e.id !== m.menu.id), m = null, h = null, v = !1, _ = !1, s.hidden = !0, l && (l.hidden = !0), p.length) await F(p[0]);
			else {
				j(), P();
				let e = new URL(window.location.href);
				e.searchParams.delete("menu"), window.history.replaceState(null, "", e);
			}
			t({
				type: "success",
				message: e.message
			});
		});
	}), r.querySelectorAll("[data-source-search]").forEach((e) => e.addEventListener("input", () => {
		let t = e.closest("form"), n = e.value.toLocaleLowerCase("tr").trim(), r = [...t.querySelectorAll(".menu-source-option")];
		r.forEach((e) => {
			e.hidden = !e.textContent.toLocaleLowerCase("tr").includes(n), e.hidden && (e.querySelector("input").checked = !1);
		}), t.querySelector("[data-source-no-results]").hidden = r.some((e) => !e.hidden);
	})), r.querySelectorAll("[data-menu-add]").forEach((e) => e.addEventListener("submit", (n) => {
		if (n.preventDefault(), !m || !A()) return;
		let r = Object.fromEntries(new FormData(e));
		r.lang_id = i.language?.id, r.target_type = e.dataset.type, r.is_active = e.elements.namedItem("is_active")?.checked ?? !0, r.new_tab = !1, r.sort_order = Math.min(65535, Math.max(-1, ...m.items.filter((e) => e.parent_id === null).map((e) => e.sort_order)) + 1), r.target_type === "content" && (r.source = e.dataset.source, r.label = e.querySelector("input[name=\"source_id\"]:checked")?.dataset.label ?? ""), k(async () => {
			let n = await O(m.urls.add, "POST", r);
			e.reset(), e.querySelectorAll(".menu-source-option").forEach((e) => {
				e.hidden = !1;
			}), e.querySelector("[data-source-no-results]") && (e.querySelector("[data-source-no-results]").hidden = !0), t({
				type: "success",
				message: n.message
			}), await I(m.menu, n.id);
		}, e);
	})), c?.addEventListener("input", () => {
		_ = !0, a("[data-item-unsaved]").hidden = !1;
	}), c?.addEventListener("change", () => {
		_ = !0, a("[data-item-unsaved]").hidden = !1;
	}), c?.addEventListener("submit", (e) => {
		e.preventDefault();
		let n = m.items.find((e) => e.id === h), r = Object.fromEntries(new FormData(c));
		r.lang_id = i.language?.id, r.is_active = c.elements.namedItem("is_active").checked, r.new_tab = c.elements.namedItem("new_tab").checked, r.dynamic_children = c.querySelector("[name=\"dynamic_children\"]").checked && !c.querySelector("[name=\"dynamic_children\"]").disabled, r.parent_id = r.parent_id ? Number(r.parent_id) : null, r.sort_order = Number(r.sort_order), k(async () => {
			t({
				type: "success",
				message: (await O(n.update_url, "PUT", r)).message
			}), await I(m.menu, n.id);
		}, c);
	}), a("[data-item-cancel]")?.addEventListener("click", () => {
		A() && (_ = !1, B(h, !1));
	});
	async function H(e) {
		let r = m.items.find((t) => t.id === e);
		r && A() && await n({
			title: "Menü öğesini sil",
			message: `“${r.label}” öğesi silinsin mi?`,
			confirmLabel: "Sil"
		}) && k(async () => {
			t({
				type: "success",
				message: (await O(r.delete_url, "DELETE", { lang_id: i.language?.id })).message
			}), await I(m.menu, h === e ? null : h);
		});
	}
	a("[data-item-delete]")?.addEventListener("click", () => H(h)), u.addEventListener("click", (e) => {
		let t = e.target.closest("[data-edit]"), n = e.target.closest("[data-delete]"), r = e.target.closest("[data-collapse]");
		if (!y && (t && B(Number(t.dataset.edit)), n && H(Number(n.dataset.delete)), r)) {
			let e = Number(r.dataset.collapse);
			x.has(e) ? x.delete(e) : x.add(e), z();
		}
	}), a("[data-menu-expand]").addEventListener("click", () => {
		x.clear(), z();
	}), a("[data-menu-collapse]").addEventListener("click", () => {
		m.items.forEach((e) => x.add(e.id)), z();
	});
	function U(e, t, n) {
		if (y || !A()) return;
		let r = L(e), a = m.items.find((e) => e.id === t);
		if (!a || r.has(t) || n === "inside" && a.dynamic_children) return;
		let o = structuredClone(m.items), s = m.items.filter((e) => r.has(e.id)), c = L(t), l = m.items.filter((e) => !r.has(e.id)), u = l.findIndex((e) => e.id === t);
		if (n !== "before") for (u++; u < l.length && c.has(l[u].id);) u++;
		s[0].parent_id = n === "inside" ? t : a.parent_id, m.items = [
			...l.slice(0, u),
			...s,
			...l.slice(u)
		];
		let d = /* @__PURE__ */ new Map();
		m.items.forEach((e) => {
			let t = e.parent_id;
			e.sort_order = d.get(t) ?? 0, d.set(t, e.sort_order + 1);
		}), x.delete(t), z(), k(async () => {
			try {
				await O(m.urls.reorder, "PUT", {
					lang_id: i.language?.id,
					items: m.items.map((e) => ({
						id: e.id,
						parent_id: e.parent_id,
						sort_order: e.sort_order
					}))
				});
			} catch (e) {
				throw m.items = o, z(), e;
			}
			await I(m.menu, h), T("Yeni sıra ve üst öğe ilişkisi kaydedildi."), b = `[data-move="${e}"]`;
		});
	}
	u.addEventListener("keydown", (e) => {
		let t = e.target.closest("[data-move]");
		if (!t || ![
			"ArrowUp",
			"ArrowDown",
			"ArrowRight",
			"ArrowLeft"
		].includes(e.key) || y) return;
		e.preventDefault();
		let n = Number(t.dataset.move), r = m.items.find((e) => e.id === n), i = m.items.filter((e) => e.parent_id === r.parent_id), a = i.findIndex((e) => e.id === n);
		e.key === "ArrowUp" && a > 0 && U(n, i[a - 1].id, "before"), e.key === "ArrowDown" && a < i.length - 1 && U(n, i[a + 1].id, "after"), e.key === "ArrowRight" && a > 0 && U(n, i[a - 1].id, "inside"), e.key === "ArrowLeft" && r.parent_id !== null && U(n, r.parent_id, "after");
	});
	let W = null, G = null;
	function K() {
		u.querySelectorAll(".drop-before, .drop-after, .drop-inside").forEach((e) => e.classList.remove("drop-before", "drop-after", "drop-inside"));
	}
	function q() {
		if (!W?.started) return;
		K(), W.target = null;
		let e = document.elementFromPoint(W.x, W.y)?.closest(".menu-row");
		if (!e || !u.contains(e)) return;
		let t = Number(e.dataset.id);
		if (L(W.id).has(t)) return;
		let n = e.getBoundingClientRect(), r = (W.y - n.top) / n.height, i = r < .27 ? "before" : r > .73 ? "after" : "inside";
		if (i === "inside" && m.items.find((e) => e.id === t).dynamic_children) return;
		W.target = {
			id: t,
			placement: i
		};
		let a = e;
		if (i === "after") {
			let n = L(t), r = [...u.querySelectorAll(".menu-row")].filter((e) => n.has(Number(e.dataset.id)));
			a = r[r.length - 1] ?? e;
		}
		a.classList.add(`drop-${i}`);
	}
	function J() {
		if (!W?.started) return;
		let e = W.y < 90 ? -9 : W.y > window.innerHeight - 90 ? 9 : 0;
		e && (window.scrollBy(0, e), q()), G = requestAnimationFrame(J);
	}
	function Y(e, t = !1) {
		if (!W || e.pointerId !== void 0 && e.pointerId !== W.pointerId) return;
		let n = W;
		W = null, G && cancelAnimationFrame(G), n.preview?.remove(), n.handle.classList.remove("is-dragging"), u.querySelectorAll(".is-dragging").forEach((e) => e.classList.remove("is-dragging")), K(), n.handle.hasPointerCapture(n.pointerId) && n.handle.releasePointerCapture(n.pointerId), !t && n.target && U(n.id, n.target.id, n.target.placement);
	}
	u.addEventListener("pointerdown", (e) => {
		let t = e.target.closest("[data-move]");
		t && !y && e.button === 0 && (e.preventDefault(), t.focus({ preventScroll: !0 }), W = {
			id: Number(t.dataset.move),
			pointerId: e.pointerId,
			startX: e.clientX,
			startY: e.clientY,
			x: e.clientX,
			y: e.clientY,
			handle: t,
			started: !1
		}, t.setPointerCapture(e.pointerId));
	}), u.addEventListener("pointermove", (e) => {
		W && W.pointerId === e.pointerId && (W.x = e.clientX, W.y = e.clientY, !(!W.started && Math.hypot(W.x - W.startX, W.y - W.startY) < 6) && (W.started || (W.started = !0, W.preview = C("div", "menu-drag-preview", m.items.find((e) => e.id === W.id).label), document.body.append(W.preview), W.handle.closest(".menu-row").classList.add("is-dragging"), G = requestAnimationFrame(J)), W.preview.style.left = `${W.x + 12}px`, W.preview.style.top = `${W.y + 12}px`, q()));
	}), u.addEventListener("pointerup", (e) => Y(e)), u.addEventListener("pointercancel", (e) => Y(e, !0)), u.addEventListener("lostpointercapture", (e) => Y(e, !0)), document.addEventListener("keydown", (e) => {
		e.key === "Escape" && W && Y({}, !0);
	}), window.addEventListener("beforeunload", (e) => {
		(_ || v) && (e.preventDefault(), e.returnValue = "");
	}), r.querySelectorAll("[data-menu-language]").forEach((e) => e.addEventListener("click", (e) => {
		(y || !A()) && e.preventDefault();
	})), o.replaceChildren(new Option(p.length ? "Menü seçin" : "Henüz menü yok", "")), p.forEach((e) => o.add(new Option(`${e.name}${e.translation_exists ? "" : " · Çeviri eklenmedi"} · ${e.key}`, String(e.id)))), P();
	let X = p.find((e) => e.id === i.initialMenu) ?? p[0];
	X ? k(async () => {
		await F(X, i.initialItem), i.newMenu && i.permissions.create && V(!0);
	}) : i.permissions.create && V(!0);
}
//#endregion
//#region Modules/Menus/resources/assets/js/menus.js
function n() {
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
function r(e) {
	t(e), n();
}
//#endregion
export { r as default };
