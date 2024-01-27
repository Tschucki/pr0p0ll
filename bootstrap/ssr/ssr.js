import { useSSRContext, mergeProps, withCtx, unref, createVNode, createTextVNode, openBlock, createBlock, createCommentVNode, resolveDirective, toDisplayString, resolveComponent, createSSRApp, h } from "vue";
import { ssrRenderAttrs, ssrRenderSlot, ssrRenderComponent, ssrRenderAttr, ssrGetDirectiveProps, ssrRenderStyle, ssrInterpolate } from "vue/server-renderer";
import { Link, Head, createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { MotionPlugin } from "@vueuse/motion";
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const _sfc_main$9 = {
  name: "Container"
};
function _sfc_ssrRender$3(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-7xl px-6 lg:px-8 relative" }, _attrs))}><div class="mx-auto max-w-2xl lg:max-w-none">`);
  ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
  _push(`</div></div>`);
}
const _sfc_setup$9 = _sfc_main$9.setup;
_sfc_main$9.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Container.vue");
  return _sfc_setup$9 ? _sfc_setup$9(props, ctx) : void 0;
};
const Container = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["ssrRender", _sfc_ssrRender$3]]);
const _sfc_main$8 = {
  name: "Button"
};
function _sfc_ssrRender$2(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  _push(`<button${ssrRenderAttrs(mergeProps({ class: "inline-flex rounded-lg px-4 py-1.5 font-semibold text-sm transition bg-pr0gramm-500 text-white hover:bg-pr0gramm-500/80" }, _attrs))}>`);
  ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
  _push(`</button>`);
}
const _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Button.vue");
  return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
const Button = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["ssrRender", _sfc_ssrRender$2]]);
const pr0p0llImage = "/build/assets/pr0p0ll-fRda7U6F.png";
const _sfc_main$7 = {
  __name: "Header",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<header${ssrRenderAttrs(_attrs)}><div class="absolute left-0 right-0 top-2 z-40 pt-14">`);
      _push(ssrRenderComponent(Container, null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          var _a, _b, _c, _d;
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}>`);
            _push2(ssrRenderComponent(unref(Link), {
              href: "/",
              class: "sm:text-2xl text-xl font-medium flex items-center gap-2 text-[#f2f5f4]"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<img alt="Pr0p0ll Logo" title="Pr0p0ll Logo"${ssrRenderAttr("src", unref(pr0p0llImage))} class="w-auto sm:h-12 h-10 aspect-square"${_scopeId2}> Pr0p0ll `);
                } else {
                  return [
                    createVNode("img", {
                      alt: "Pr0p0ll Logo",
                      title: "Pr0p0ll Logo",
                      src: unref(pr0p0llImage),
                      class: "w-auto sm:h-12 h-10 aspect-square"
                    }, null, 8, ["src"]),
                    createTextVNode(" Pr0p0ll ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="flex items-center gap-x-8"${_scopeId}>`);
            if (!((_a = _ctx.$page.props.auth.user) == null ? void 0 : _a.id)) {
              _push2(`<a href="/login"${_scopeId}>`);
              _push2(ssrRenderComponent(Button, null, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Login mit Pr0gramm `);
                  } else {
                    return [
                      createTextVNode(" Login mit Pr0gramm ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</a>`);
            } else {
              _push2(`<!---->`);
            }
            if ((_b = _ctx.$page.props.auth.user) == null ? void 0 : _b.id) {
              _push2(`<a href="/login"${_scopeId}>`);
              _push2(ssrRenderComponent(Button, null, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Zu den Umfragen `);
                  } else {
                    return [
                      createTextVNode(" Zu den Umfragen ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</a>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode(unref(Link), {
                  href: "/",
                  class: "sm:text-2xl text-xl font-medium flex items-center gap-2 text-[#f2f5f4]"
                }, {
                  default: withCtx(() => [
                    createVNode("img", {
                      alt: "Pr0p0ll Logo",
                      title: "Pr0p0ll Logo",
                      src: unref(pr0p0llImage),
                      class: "w-auto sm:h-12 h-10 aspect-square"
                    }, null, 8, ["src"]),
                    createTextVNode(" Pr0p0ll ")
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "flex items-center gap-x-8" }, [
                  !((_c = _ctx.$page.props.auth.user) == null ? void 0 : _c.id) ? (openBlock(), createBlock("a", {
                    key: 0,
                    href: "/login"
                  }, [
                    createVNode(Button, null, {
                      default: withCtx(() => [
                        createTextVNode(" Login mit Pr0gramm ")
                      ]),
                      _: 1
                    })
                  ])) : createCommentVNode("", true),
                  ((_d = _ctx.$page.props.auth.user) == null ? void 0 : _d.id) ? (openBlock(), createBlock("a", {
                    key: 1,
                    href: "/login"
                  }, [
                    createVNode(Button, null, {
                      default: withCtx(() => [
                        createTextVNode(" Zu den Umfragen ")
                      ]),
                      _: 1
                    })
                  ])) : createCommentVNode("", true)
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></header>`);
    };
  }
};
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Header.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const _sfc_main$6 = {
  __name: "Footer",
  __ssrInlineRender: true,
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _directive_motion = resolveDirective("motion");
      _push(`<footer${ssrRenderAttrs(mergeProps({
        class: "mx-auto max-w-7xl px-6 lg:px-8 mt-24 w-full sm:pt-32 lg:pt-40",
        initial: { opacity: 0, y: 24 },
        visibleOnce: { opacity: 1, y: 0 },
        delay: 100
      }, _attrs, ssrGetDirectiveProps(_ctx, _directive_motion)))}><div class="mx-auto max-w-2xl lg:max-w-none"><div style="${ssrRenderStyle({ "opacity": "1", "transform": "none" })}"><div class="grid grid-cols-1 gap-x-8 gap-y-16 lg:grid-cols-2"><nav><ul role="list" class="grid grid-cols-2 gap-8 sm:grid-cols-3"><li><div class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">Rechtliches</div><ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80"><li class="mt-4">`);
      _push(ssrRenderComponent(unref(Link), {
        class: "transition hover:text-[#f2f5f4]",
        href: "/impressum"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`Impressum`);
          } else {
            return [
              createTextVNode("Impressum")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</li><li class="mt-4">`);
      _push(ssrRenderComponent(unref(Link), {
        class: "transition hover:text-[#f2f5f4]",
        href: "/datenschutz"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`Datenschutz`);
          } else {
            return [
              createTextVNode("Datenschutz")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</li><li class="mt-4"><a class="transition hover:text-[#f2f5f4]" href="/nutzungsbedingungen">Nutzungsbedingungen</a></li></ul></li><li><div class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">Hilfe</div><ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80"><li class="mt-4"><a class="transition hover:text-[#f2f5f4]" href="/">FAQ</a></li><li class="mt-4"><a class="transition hover:text-[#f2f5f4]" href="/">Diskussionen</a></li><li class="mt-4"><a class="transition hover:text-[#f2f5f4]" href="/">Auswertungen</a></li></ul></li><li><div class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">Kontakt</div><ul role="list" class="mt-4 text-sm text-[#f2f5f4]/80"><li class="mt-4"><a class="transition hover:text-[#f2f5f4]" href="https://pr0gramm.com/inbox/messages/PimmelmannJones">PimmelmannJones (Pr0gramm)</a></li></ul></li></ul></nav><div class="flex lg:justify-end"><form class="max-w-sm"><h2 class="font-display text-sm font-semibold tracking-wider text-[#f2f5f4]">Lass einen Stern auf Github da</h2><p class="mt-4 text-sm text-[#f2f5f4]/80">Zeige, dass dir das Projekt gefällt und gib dem Repo einen Stern</p><a target="_blank" href="https://github.com/pr0p0ll/pr0p0ll" class="flex aspect-video h-12 gap-4 w-full mt-6 items-center justify-center rounded-xl bg-pr0gramm-500 text-white hover:bg-pr0gramm-500/80 transition hover:bg-neutral-800 font-semibold"><span class="tracking-wide">Liebe teilen</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"></path></svg></a></form></div></div><div class="mb-20 mt-24 flex flex-wrap items-end justify-between gap-x-6 gap-y-4 border-t border-neutral-950/10 pt-12"><a aria-label="Home" href="/" class="text-[#f2f5f4] flex items-center gap-2 font-medium"><img alt="Pr0p0ll Logo" title="Pr0p0ll Logo"${ssrRenderAttr("src", unref(pr0p0llImage))} class="w-auto sm:h-8 h-4 aspect-square"> Pr0p0ll </a><p class="text-sm text-[#f2f5f4]">ԅ(≖‿≖ԅ)</p></div></div></div></footer>`);
    };
  }
};
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/Footer.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = {
  __name: "Layout",
  __ssrInlineRender: true,
  props: {
    title: String,
    description: String
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(_attrs)}>`);
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<title${_scopeId}>${ssrInterpolate(__props.title)}</title><meta name="description"${ssrRenderAttr("content", __props.description)}${_scopeId}>`);
          } else {
            return [
              createVNode("title", null, toDisplayString(__props.title), 1),
              createVNode("meta", {
                name: "description",
                content: __props.description
              }, null, 8, ["content"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_sfc_main$7, null, null, _parent));
      _push(`<div class="relative flex flex-auto overflow-hidden bg-[#161618] pt-14"><div class="relative isolate flex w-full flex-col pt-9"><svg aria-hidden="true" class="absolute inset-x-0 -top-14 -z-10 h-[1000px] w-full fill-[transparent] stroke-[#f2f5f4]/20 [mask-image:linear-gradient(to_bottom_left,white_40%,transparent_50%)]"><rect width="100%" height="100%" fill="url(#:rd:)" stroke-width="0"></rect><svg x="50%" y="-96" stroke-width="0" class="overflow-visible"><path transform="translate(64 160)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path><path transform="translate(128 320)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path><path transform="translate(288 480)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path><path transform="translate(512 320)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path><path transform="translate(544 640)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path><path transform="translate(320 800)" d="M45.119 4.5a11.5 11.5 0 0 0-11.277 9.245l-25.6 128C6.82 148.861 12.262 155.5 19.52 155.5h63.366a11.5 11.5 0 0 0 11.277-9.245l25.6-128c1.423-7.116-4.02-13.755-11.277-13.755H45.119Z"></path></svg><defs><pattern id=":rd:" width="96" height="480" x="50%" patternUnits="userSpaceOnUse" patternTransform="translate(0 -96)" fill="none"><path d="M128 0 98.572 147.138A16 16 0 0 1 82.883 160H13.117a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-45.117 320H-116M64-160 34.572-12.862A16 16 0 0 1 18.883 0h-69.766a16 16 0 0 0-15.69 12.862l-26.855 134.276A16 16 0 0 1-109.117 160H-180M192 160l-29.428 147.138A15.999 15.999 0 0 1 146.883 320H77.117a16 16 0 0 0-15.69 12.862L34.573 467.138A16 16 0 0 1 18.883 480H-52M-136 480h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1-18.883 320h69.766a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 109.117 160H192M-72 640h58.883a16 16 0 0 0 15.69-12.862l26.855-134.276A16 16 0 0 1 45.117 480h69.766a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A15.999 15.999 0 0 1 173.117 320H256M-200 320h58.883a15.999 15.999 0 0 0 15.689-12.862l26.856-134.276A16 16 0 0 1-82.883 160h69.766a16 16 0 0 0 15.69-12.862L29.427 12.862A16 16 0 0 1 45.117 0H128"></path></pattern></defs></svg><main class="w-full flex-auto mb-6">`);
      ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      _push(`</main></div></div>`);
      _push(ssrRenderComponent(_sfc_main$6, null, null, _parent));
      _push(`</div>`);
    };
  }
};
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Layouts/Layout.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ Object.assign({
  layout: (h2, page) => h2(_sfc_main$5, {
    title: "Impressum - Pr0p0ll"
  }, () => page)
}, {
  __name: "Imprint",
  __ssrInlineRender: true,
  props: {
    imprint: String
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _directive_motion = resolveDirective("motion");
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56" }, _attrs))}><div class="mx-auto max-w-2xl lg:max-w-none"><div${ssrRenderAttrs(mergeProps({
        class: "max-w-3xl",
        initial: { opacity: 0, y: 100 },
        enter: { opacity: 1, y: 0, scale: 1 },
        delay: 150
      }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><h1 class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl"> Impressum </h1></div></div><div class="prose prose-invert mt-16 prose-p:text-[#f2f5f4]/80">${__props.imprint}</div></div>`);
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Frontend/Imprint.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$4
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$3 = {
  name: "FeatureBlock"
};
function _sfc_ssrRender$1(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _directive_motion = resolveDirective("motion");
  _push(`<div${ssrRenderAttrs(mergeProps({
    class: "flex",
    style: { "opacity": "1", "transform": "none" }
  }, _attrs))}><div${ssrRenderAttrs(mergeProps({
    enter: { opacity: 1, y: 0, scale: 1 },
    hovered: { scale: 1.1 },
    delay: 100,
    class: "relative flex w-full flex-col rounded-3xl p-6 ring-1 ring-[#f2f5f4] transition sm:p-8"
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><div class="pointer-events-none text-[#f2f5f4] font-medium">`);
  ssrRenderSlot(_ctx.$slots, "image", {}, null, _push, _parent);
  _push(`</div><h2 class="mt-6 font-display text-2xl font-semibold pointer-events-none text-[#f2f5f4]">`);
  ssrRenderSlot(_ctx.$slots, "feature", {}, null, _push, _parent);
  _push(`</h2><p class="mt-4 text-base pointer-events-none text-[#f2f5f4]/80">`);
  ssrRenderSlot(_ctx.$slots, "description", {}, null, _push, _parent);
  _push(`</p></div></div>`);
}
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/FeatureBlock.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const FeatureBlock = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["ssrRender", _sfc_ssrRender$1]]);
const _sfc_main$2 = {
  name: "Landing",
  layout: (h2, page) => h2(_sfc_main$5, {
    title: "Pr0p0ll - Umfragen für Pr0gramm Nutzer",
    description: "Ein Reboot der alten Pr0p0ll-Plattform für Pr0gramm-Nutzer, um Umfragen zu erstellen und zu beantworten. Das Projekt ist Open-Source und wird von Tschucki maintaint"
  }, () => page),
  props: {
    userCount: Number,
    pollCount: Number
  },
  components: { Footer: _sfc_main$6, FeatureBlock, Layout: _sfc_main$5 },
  methods: {
    scrollToFeatures: () => {
      document.getElementById("aktuelle-features").scrollIntoView({ behavior: "smooth" });
    }
  }
};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_FeatureBlock = resolveComponent("FeatureBlock");
  const _directive_motion = resolveDirective("motion");
  _push(`<!--[--><div class="mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56"><div class="mx-auto max-w-2xl lg:max-w-none"><div${ssrRenderAttrs(mergeProps({
    class: "max-w-3xl",
    initial: { opacity: 0, y: 100 },
    enter: { opacity: 1, y: 0, scale: 1 },
    delay: 150
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><h1 class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl"> Pr0p0ll ist endlich zurück! </h1><p class="mt-6 text-xl text-[#f2f5f4]/80"> Ein Reboot der alten Pr0p0ll-Plattform für <a href="https://pr0gramm.com">Pr0gramm</a>-Nutzer, um Umfragen zu erstellen und zu beantworten. Das Projekt ist komplett Open-Source und ist auf <a href="https://github.com/pr0p0ll/pr0p0ll" target="_blank">Github</a> einsehbar </p></div></div><div class="mx-auto max-w-7xl lg:px-8 mt-16"><div class="mx-auto max-w-2xl lg:max-w-none"><div><dl class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:auto-cols-fr lg:grid-flow-col lg:grid-cols-none"><a${ssrRenderAttrs(mergeProps({
    href: "https://github.com/Tschucki",
    target: "_blank",
    initial: { opacity: 0, y: 100 },
    enter: { opacity: 1, y: 0, scale: 1 },
    delay: 300
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><div class="flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px" style="${ssrRenderStyle({ "opacity": "1", "transform": "none" })}"><dt class="mt-2 text-base text-[#f2f5f4]/80">Maintainer</dt><dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">Tschucki</dd></div></a><div${ssrRenderAttrs(mergeProps({
    initial: { opacity: 0, y: 100 },
    enter: { opacity: 1, y: 0, scale: 1 },
    delay: 500,
    class: "flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px",
    style: { "opacity": "1", "transform": "none" }
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><dt class="mt-2 text-base text-[#f2f5f4]/80">Bisher erstelle Umfragen</dt><dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">${ssrInterpolate($props.pollCount)}</dd></div><div${ssrRenderAttrs(mergeProps({
    initial: { opacity: 0, y: 100 },
    enter: { opacity: 1, y: 0, scale: 1 },
    delay: 700,
    class: "flex flex-col-reverse pl-8 relative before:absolute after:absolute before:bg-[#f2f5f4] after:bg-[#f2f5f4]/20 before:left-0 before:top-0 before:h-6 before:w-px after:bottom-0 after:left-0 after:top-8 after:w-px",
    style: { "opacity": "1", "transform": "none" }
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><dt class="mt-2 text-base text-[#f2f5f4]/80">Aktive Nutzer</dt><dd class="font-display text-3xl font-semibold text-[#f2f5f4] sm:text-4xl">${ssrInterpolate($props.userCount)}</dd></div></dl></div></div></div><div${ssrRenderAttrs(mergeProps({
    class: "sm:flex hidden flex-col items-center gap-2 mt-12 cursor-pointer",
    initial: { opacity: 0, y: 100 },
    enter: { opacity: 1, y: 0, scale: 1 },
    delay: 900
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><p class="text-base text-[#f2f5f4]/80">Weiter</p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-[#f2f5f4]"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg></div></div><div${ssrRenderAttrs(mergeProps({
    id: "aktuelle-features",
    class: "mx-auto max-w-7xl px-6 lg:px-8 pt-20 sm:mt-32 sm:py-32 lg:mt-42",
    initial: { opacity: 0, y: 24 },
    visibleOnce: { opacity: 1, y: 0, scale: 1 },
    delay: 300
  }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><div class="mx-auto max-w-2xl lg:max-w-none"><div class="max-w-2xl" style="${ssrRenderStyle({ "opacity": "1", "transform": "none" })}"><h2><span class="block font-display tracking-tight [text-wrap:balance] text-4xl font-medium sm:text-5xl text-[#f2f5f4]">Aktuelle Features und Neuerungen</span></h2><div class="mt-6 text-xl text-[#f2f5f4]/80"><p>Hier eine kurze Liste an Neuerungen, die das neue Pr0p0ll mitbringt. Das Projekt befindet sich in stetiger Entwicklung und ist offen für jegliche Kritik und Hilfe</p></div></div></div></div><div class="mx-auto max-w-7xl px-6 lg:px-8 mt-16"><div class="mx-auto max-w-2xl lg:max-w-none"><div class="grid grid-cols-1 gap-8 lg:grid-cols-3">`);
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 200
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` UI/UX `);
      } else {
        return [
          createTextVNode(" UI/UX ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Form-Builder `);
      } else {
        return [
          createTextVNode(" Form-Builder ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Der Form-Builder ermöglicht es euch, Umfragen mit verschiedenen Fragetypen und Antworttypen zusammenzustellen.<br${_scopeId}><br${_scopeId}>So könnt ihr euch eure Umfragen auch schon vor Veröffentlichung anschauen. `);
      } else {
        return [
          createTextVNode(" Der Form-Builder ermöglicht es euch, Umfragen mit verschiedenen Fragetypen und Antworttypen zusammenzustellen."),
          createVNode("br"),
          createVNode("br"),
          createTextVNode("So könnt ihr euch eure Umfragen auch schon vor Veröffentlichung anschauen. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 300
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` UI/UX `);
      } else {
        return [
          createTextVNode(" UI/UX ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Zielgruppen definieren `);
      } else {
        return [
          createTextVNode(" Zielgruppen definieren ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Ihr könnt eure Umfragen nun auch für noch spezifischere Zielgruppen freigeben. Ihr könnt das Geschlecht, das Alter und die Region des Pr0gramm-Nutzers definieren, der an eure Umfrage teilnehmen darf. <br${_scopeId}><br${_scopeId}> Man sieht auch wie viele Nutzer in eure Zielgruppe fallen. `);
      } else {
        return [
          createTextVNode(" Ihr könnt eure Umfragen nun auch für noch spezifischere Zielgruppen freigeben. Ihr könnt das Geschlecht, das Alter und die Region des Pr0gramm-Nutzers definieren, der an eure Umfrage teilnehmen darf. "),
          createVNode("br"),
          createVNode("br"),
          createTextVNode(" Man sieht auch wie viele Nutzer in eure Zielgruppe fallen. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 400
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` UX `);
      } else {
        return [
          createTextVNode(" UX ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` O-Auth `);
      } else {
        return [
          createTextVNode(" O-Auth ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Ihr könnt euch nun direkt mit eurem Pr0gramm-Account auf Pr0p0ll anmelden. Ihr braucht euch also kein neues Konto bei Pr0p0ll anzulegen. `);
      } else {
        return [
          createTextVNode(" Ihr könnt euch nun direkt mit eurem Pr0gramm-Account auf Pr0p0ll anmelden. Ihr braucht euch also kein neues Konto bei Pr0p0ll anzulegen. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 450
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` UX `);
      } else {
        return [
          createTextVNode(" UX ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Benachrichtigungssystem `);
      } else {
        return [
          createTextVNode(" Benachrichtigungssystem ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Alle Nutzer haben die Möglichkeit Benachrichtigungen für relevante Ereignisse zu aktivieren. So werdet ihr über neue Umfragen, abgeschlossene Umfragen und Antworten auf eure Umfragen benachrichtigt. `);
      } else {
        return [
          createTextVNode(" Alle Nutzer haben die Möglichkeit Benachrichtigungen für relevante Ereignisse zu aktivieren. So werdet ihr über neue Umfragen, abgeschlossene Umfragen und Antworten auf eure Umfragen benachrichtigt. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 500
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Nice-To-Haves `);
      } else {
        return [
          createTextVNode(" Nice-To-Haves ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Bewertung von Umfragen `);
      } else {
        return [
          createTextVNode(" Bewertung von Umfragen ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Am Ende einer Umfrage könnt ihr diese bewerten. So können andere Nutzer sehen, wie gut eure Umfrage war und ihr könnt sehen, wie gut eure Umfragen im Vergleich zu anderen Umfragen abgeschnitten haben. `);
      } else {
        return [
          createTextVNode(" Am Ende einer Umfrage könnt ihr diese bewerten. So können andere Nutzer sehen, wie gut eure Umfrage war und ihr könnt sehen, wie gut eure Umfragen im Vergleich zu anderen Umfragen abgeschnitten haben. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(ssrRenderComponent(_component_FeatureBlock, mergeProps({
    initial: { opacity: 0, x: 24 },
    visibleOnce: { opacity: 1, x: 0 },
    delay: 550
  }, ssrGetDirectiveProps(_ctx, _directive_motion)), {
    image: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Mehr `);
      } else {
        return [
          createTextVNode(" Mehr ")
        ];
      }
    }),
    feature: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Alle Features `);
      } else {
        return [
          createTextVNode(" Alle Features ")
        ];
      }
    }),
    description: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(` Hier findet ihr eine Liste aller Features, die das neue Pr0p0ll mitbringt. `);
      } else {
        return [
          createTextVNode(" Hier findet ihr eine Liste aller Features, die das neue Pr0p0ll mitbringt. ")
        ];
      }
    }),
    _: 1
  }, _parent));
  _push(`</div></div></div><!--]-->`);
}
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Frontend/Landing.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const Landing = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["ssrRender", _sfc_ssrRender]]);
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Landing
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$1 = /* @__PURE__ */ Object.assign({
  layout: (h2, page) => h2(_sfc_main$5, {
    title: "Datenschutzerklärung - Pr0p0ll"
  }, () => page)
}, {
  __name: "Privacy",
  __ssrInlineRender: true,
  props: {
    privacy: String
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _directive_motion = resolveDirective("motion");
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56" }, _attrs))}><div class="mx-auto max-w-2xl lg:max-w-none"><div${ssrRenderAttrs(mergeProps({
        class: "max-w-3xl",
        initial: { opacity: 0, y: 100 },
        enter: { opacity: 1, y: 0, scale: 1 },
        delay: 150
      }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><h1 class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl"><span class="hidden sm:flex">Datenschutzerklärung</span><span class="sm:hidden flex">Datenschutz-erklärung</span></h1></div></div><div class="prose prose-invert mt-16 prose-p:text-[#f2f5f4]/80">${__props.privacy}</div></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Frontend/Privacy.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$1
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main = /* @__PURE__ */ Object.assign({
  layout: (h2, page) => h2(_sfc_main$5, {
    title: "Nutzungsbedingungen - Pr0p0ll"
  }, () => page)
}, {
  __name: "Terms",
  __ssrInlineRender: true,
  props: {
    terms: String
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _directive_motion = resolveDirective("motion");
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-7xl px-6 lg:px-8 mt-24 sm:mt-32 md:mt-56" }, _attrs))}><div class="mx-auto max-w-2xl lg:max-w-none"><div${ssrRenderAttrs(mergeProps({
        class: "max-w-3xl",
        initial: { opacity: 0, y: 100 },
        enter: { opacity: 1, y: 0, scale: 1 },
        delay: 150
      }, ssrGetDirectiveProps(_ctx, _directive_motion)))}><h1 class="font-display text-5xl font-medium tracking-tight text-[#f2f5f4] [text-wrap:balance] sm:text-7xl"><span class="hidden sm:flex">Nutzungsbedingungen</span><span class="sm:hidden flex">Nutzungs-bedingungen</span></h1></div></div><div class="prose prose-invert mt-16 prose-p:text-[#f2f5f4]/80">${__props.terms}</div></div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Frontend/Terms.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main
}, Symbol.toStringTag, { value: "Module" }));
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    resolve: (name) => {
      const pages = /* @__PURE__ */ Object.assign({ "./Pages/Frontend/Imprint.vue": __vite_glob_0_0, "./Pages/Frontend/Landing.vue": __vite_glob_0_1, "./Pages/Frontend/Privacy.vue": __vite_glob_0_2, "./Pages/Frontend/Terms.vue": __vite_glob_0_3 });
      return pages[`./Pages/${name}.vue`];
    },
    setup({ App, props, plugin }) {
      return createSSRApp({
        render: () => h(App, props)
      }).use(plugin).use(MotionPlugin);
    }
  })
);
