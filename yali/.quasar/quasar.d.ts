/* oxlint-disable */
/* eslint-disable */
/// <reference types="@quasar/app-vite" />
/// <reference types="@quasar/app-vite/client" />

// https://quasar.dev/quasar-cli-vite/handling-import-meta-env#type-inference

// Automatically generated from raw build.define
declare const __VUE_PROD_DEVTOOLS__: boolean;
declare const __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: boolean;
declare const __VUE_I18N_FULL_INSTALL__: boolean;
declare const __VUE_I18N_LEGACY_API__: boolean;
declare const __VUE_I18N_PROD_DEVTOOLS__: boolean;
declare const __INTLIFY_PROD_DEVTOOLS__: boolean;
declare const __VUE_OPTIONS_API__: boolean;

// Automatically generated from process.env & dotenv files & build.define & build.defineEnv;
// Backend-only are not available in client code, so they are marked as optional
interface ImportMetaEnv {
  readonly PRODUCT_NAME?: string;
  readonly VITE_BUILD_TAG?: string;
  readonly SHELL?: string;
  readonly HISTCONTROL?: string;
  readonly HOSTNAME?: string;
  readonly HISTSIZE?: number;
  readonly GPG_TTY?: string;
  readonly EDITOR?: string;
  readonly PWD?: string;
  readonly LOGNAME?: string;
  readonly XDG_SESSION_TYPE?: string;
  readonly _?: string;
  readonly MOTD_SHOWN?: string;
  readonly HOME?: string;
  readonly LANG?: string;
  readonly LS_COLORS?: string;
  readonly SSH_CONNECTION?: string;
  readonly MOZ_GMP_PATH?: string;
  readonly XDG_SESSION_CLASS?: string;
  readonly SELINUX_ROLE_REQUESTED?: string;
  readonly TERM?: string;
  readonly LESSOPEN?: string;
  readonly USER?: string;
  readonly SELINUX_USE_CURRENT_RANGE?: string;
  readonly SHLVL?: number;
  readonly XDG_SESSION_ID?: number;
  readonly XDG_RUNTIME_DIR?: string;
  readonly SSH_CLIENT?: string;
  readonly DEBUGINFOD_URLS?: string;
  readonly DEBUGINFOD_IMA_CERT_PATH?: string;
  readonly XDG_DATA_DIRS?: string;
  readonly PATH?: string;
  readonly SELINUX_LEVEL_REQUESTED?: string;
  readonly DBUS_SESSION_BUS_ADDRESS?: string;
  readonly MAIL?: string;
  readonly SSH_TTY?: string;
  readonly OLDPWD?: string;
  readonly QUASAR_CLI_VERSION?: string;
  readonly NODE_ENV?: string;
}
