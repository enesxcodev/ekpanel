//#region Modules/Store/resources/assets/js/panel.js
function e(e) {
	let t = document.querySelector("[data-store-form]");
	if (!t) return;
	let n = (e, t = []) => {
		try {
			return JSON.parse(e?.value || e?.textContent || "[]") || t;
		} catch {
			return t;
		}
	}, r = (e, t = "", n = "text") => {
		let r = document.createElement("input");
		return r.name = e, r.value = t ?? "", r.type = n, r.className = "form-control", r;
	}, i = (e, t) => {
		let n = document.createElement("button");
		return n.type = "button", n.className = "btn btn-sm btn-outline-secondary", n.textContent = e, n.addEventListener("click", t), n;
	}, a = (e, t) => {
		let n = document.createElement("label");
		return n.className = "d-block mb-2", n.append(document.createTextNode(e), t), n;
	}, o = [...t.querySelectorAll("[data-bs-toggle=\"tab\"]")], s = "store.form.tab." + t.dataset.formKey, c = (t) => {
		e?.bootstrap?.Tab ? e.bootstrap.Tab.getOrCreateInstance(t).show() : t.click();
	};
	o.forEach((e) => e.addEventListener("shown.bs.tab", () => sessionStorage.setItem(s, e.dataset.bsTarget)));
	let l = t.querySelector(".is-invalid, [aria-invalid=\"true\"]")?.closest(".tab-pane"), u = t.dataset.errorTab ? "#store-" + t.dataset.errorTab : l ? "#" + l.id : sessionStorage.getItem(s), d = o.find((e) => e.dataset.bsTarget === u);
	d && c(d), t.closest("form").addEventListener("invalid", (e) => {
		let t = e.target.closest(".tab-pane"), n = o.find((e) => e.dataset.bsTarget === "#" + t?.id);
		n && c(n);
	}, !0);
	let f = t.querySelector("[data-store-gallery]"), p = f.querySelector("[name=\"image_media_ids\"]"), m = new Map([...f.querySelectorAll("[data-id]")].map((e) => [Number(e.dataset.id), e.dataset.name])), h = f.querySelector("[data-gallery-order]"), g = () => {
		let e = n(p);
		h.replaceChildren(), e.forEach((t, n) => {
			let r = document.createElement("li");
			r.className = "list-group-item d-flex gap-2 align-items-center";
			let a = document.createElement("span");
			a.className = "me-auto", a.textContent = m.get(Number(t)) || "Görsel #" + t, r.append(a), [["Yukarı", -1], ["Aşağı", 1]].forEach(([t, a]) => {
				let o = i(t, () => {
					[e[n], e[n + a]] = [e[n + a], e[n]], p.value = JSON.stringify(e), p.dispatchEvent(new Event("change", { bubbles: !0 }));
				});
				o.disabled = n + a < 0 || n + a >= e.length, r.append(o);
			}), h.append(r);
		});
	};
	p.addEventListener("change", g), f.addEventListener("media:selected", (e) => {
		e.detail.files.forEach((e) => m.set(Number(e.id), e.name)), g(), C();
	}), g();
	let _ = t.querySelector("[data-variant-editor]"), v = n(_.querySelector("[data-groups-json]")), y = n(_.querySelector("[data-variants-json]")), b = _.querySelector("[data-option-groups]"), x = _.querySelector("[data-variant-rows]"), S = (e) => JSON.stringify(Object.entries(e).sort(([e], [t]) => e.localeCompare(t))), C = () => {
		x.querySelectorAll("select[data-variant-image]").forEach((e) => {
			let t = e.value, r = [.../* @__PURE__ */ new Set([...n(p), ...t ? [Number(t)] : []])];
			e.replaceChildren(new Option("Ürün görseli", "")), r.forEach((t) => e.add(new Option(m.get(Number(t)) || "Görsel #" + t, t))), e.value = t;
		});
	}, w = () => {
		b.replaceChildren(), v.forEach((e, t) => {
			let n = document.createElement("div");
			n.className = "border rounded p-3 mb-2";
			let o = r("option_groups[" + t + "][name]", e.name);
			o.addEventListener("input", () => e.name = o.value);
			let s = r("option_groups[" + t + "][values]", e.values);
			s.addEventListener("input", () => e.values = s.value), n.append(a("Grup adı", o), a("Değerler (virgülle ayırın)", s), i("Grubu kaldır", () => {
				v.splice(t, 1), w();
			})), b.append(n);
		});
	}, T = () => {
		x.replaceChildren(), y.forEach((e, t) => {
			let n = document.createElement("div");
			n.className = "border rounded p-3 mb-3";
			let o = document.createElement("h3");
			o.className = "h4", o.textContent = Object.values(e.option_values).join(" / "), n.append(o), e.id && n.append(r("variants[" + t + "][id]", e.id, "hidden")), Object.entries(e.option_values).forEach(([e, i]) => n.append(r("variants[" + t + "][option_values][" + e + "]", i, "hidden"))), [
				[
					"sku",
					"SKU",
					"text"
				],
				[
					"price",
					"Fiyat (TL, boşsa temel fiyat)",
					"text"
				],
				[
					"stock_quantity",
					"Stok",
					"number"
				]
			].forEach(([i, o, s]) => {
				let c = r("variants[" + t + "][" + i + "]", e[i], s);
				c.addEventListener("input", () => e[i] = c.value), n.append(a(o, c));
			});
			let s = document.createElement("select");
			s.className = "form-select", s.name = "variants[" + t + "][is_active]", s.add(new Option("Aktif", "1")), s.add(new Option("Pasif", "0")), s.value = String(e.is_active ?? 1), s.addEventListener("change", () => e.is_active = s.value), n.append(a("Durum", s));
			let c = document.createElement("select");
			c.className = "form-select", c.name = "variants[" + t + "][image_media_id]", c.dataset.variantImage = "", c.add(new Option("Ürün görseli", "")), e.image_media_id && c.add(new Option("Görsel #" + e.image_media_id, e.image_media_id)), c.value = e.image_media_id || "", c.addEventListener("change", () => e.image_media_id = c.value), n.append(a("Varyant görseli (galeriden)", c));
			let l = document.createElement("div");
			l.className = "d-flex gap-2", l.append(i("Kaldır", () => {
				y.splice(t, 1), T();
			})), [-1, 1].forEach((e) => {
				let n = i(e < 0 ? "Yukarı" : "Aşağı", () => {
					[y[t], y[t + e]] = [y[t + e], y[t]], T();
				});
				n.disabled = t + e < 0 || t + e >= y.length, l.append(n);
			}), n.append(l), x.append(n);
		}), C();
	};
	_.querySelector("[data-add-group]").addEventListener("click", () => {
		v.length < 3 && (v.push({
			name: "",
			values: ""
		}), w());
	}), _.querySelector("[data-generate]").addEventListener("click", () => {
		if (!v.length || v.some((e) => !e.name.trim() || !e.values.trim() || /[\[\]]/.test(e.name))) {
			alert("Grup adını ve değerlerini doldurun.");
			return;
		}
		let e = v.reduce((e, t) => e.flatMap((e) => [...new Set(t.values.split(",").map((e) => e.trim()).filter(Boolean))].map((n) => ({
			...e,
			[t.name.trim()]: n
		}))), [{}]);
		if (e.length > 100) {
			alert("En fazla 100 kombinasyon oluşturulabilir.");
			return;
		}
		let n = new Map(y.map((e) => [S(e.option_values), e])), r = t.querySelector("[name=\"sku\"]").value;
		y = e.map((e, t) => n.get(S(e)) || {
			option_values: e,
			sku: r + "-" + (t + 1),
			price: "",
			stock_quantity: 0,
			is_active: 1
		}), T();
	}), w(), T();
	let E = t.querySelector("[data-store-catalog]");
	if (E) {
		let e = null, n = E.querySelector("[data-results]"), r = E.querySelector("[data-next]"), a = async (a) => {
			n.textContent = "Aranıyor...";
			try {
				let o = await fetch(a, { headers: { Accept: "application/json" } });
				if (!o.ok) throw Error("Katalog okunamadı.");
				let s = await o.json();
				n.replaceChildren(), e = s.next, r.hidden = !e, s.items.length || (n.textContent = "Ürün bulunamadı."), s.items.forEach((e) => {
					let r = document.createElement("div");
					r.className = "border rounded p-3 mb-2";
					let a = document.createElement("strong");
					if (a.textContent = e.title, r.append(a), e.image_url) {
						let t = document.createElement("img");
						t.src = e.image_url, t.alt = e.title, t.width = 72, t.className = "mx-2", r.append(t);
					}
					let o = document.createElement("p");
					o.textContent = e.short_description || "", r.append(o);
					let s = document.createElement("p");
					if (s.textContent = "Galeri: " + e.images.length + " görsel", r.append(s), e.existing_url) {
						let t = document.createElement("a");
						t.href = e.existing_url, t.textContent = "Daha önce aktarılmış ürünü aç", r.append(t);
					} else r.append(i("İçeriği aktar", () => {
						if (t.querySelector("[name=\"title\"]").value && !confirm("Formdaki ürün içeriği katalog içeriğiyle değiştirilsin mi?")) return;
						[
							"title",
							"slug",
							"short_description",
							"content"
						].forEach((n) => {
							let r = t.querySelector("[name=\"" + n + "\"]");
							r.value = e[n] || "", n === "content" && window.hugerte && window.hugerte.get(r.id)?.setContent(e.content || "");
						}), t.querySelector("[name=\"source_product_id\"]").value = e.id;
						let r = t.querySelector("[name=\"image_media_id\"]");
						r.value = e.image_media_id || "", r.dispatchEvent(new Event("change", { bubbles: !0 }));
						let i = t.querySelector("[name=\"remove_image\"]");
						i && (i.value = "0");
						let a = r.closest("[data-media-field]"), o = a.querySelector("[data-media-field-preview]");
						if (o.replaceChildren(), e.image_url) {
							let t = document.createElement("img");
							t.src = e.image_url, t.alt = e.title, o.append(t);
						}
						a.querySelector("[data-media-clear]").classList.toggle("d-none", !e.image_url), e.images.forEach((e) => m.set(Number(e.id), e.name)), p.value = JSON.stringify(e.images.map((e) => e.id)), p.dispatchEvent(new Event("change", { bubbles: !0 })), C(), n.textContent = e.title + ": kapak ve galeri dahil içerik aktarıldı. SKU, fiyat ve stok bilgilerini doldurun.";
					}));
					n.append(r);
				});
			} catch (e) {
				n.textContent = e.message;
			}
		};
		E.querySelector("[data-search]").addEventListener("click", () => {
			let e = new URL(E.dataset.url, location.origin);
			e.searchParams.set("q", E.querySelector("input").value), a(e);
		}), r.addEventListener("click", () => {
			e && a(e);
		});
	}
}
//#endregion
export { e as default };
