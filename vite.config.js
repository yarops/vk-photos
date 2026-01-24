import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
	build: {
		outDir: 'dist',
		emptyOutDir: true,
		rollupOptions: {
			input: {
				blocks: resolve(__dirname, 'sources/scripts/blocks.js'),
				fresh: resolve(__dirname, 'sources/scripts/fresh.js'),
				light: resolve(__dirname, 'sources/scripts/light.js'),
				admin: resolve(__dirname, 'sources/scripts/admin.js'),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: 'chunks/[name]-[hash].js',
				assetFileNames: (assetInfo) => {
					if (assetInfo.name.endsWith('.css')) {
						return '[name].css';
					}
					if (/\.(png|jpe?g|gif|svg|webp|avif)$/.test(assetInfo.name)) {
						return 'images/[name]-[hash][extname]';
					}
					return 'assets/[name]-[hash][extname]';
				},
			},
			external: [],
		},
		manifest: true,
	},
	css: {
		preprocessorOptions: {
			scss: {
				additionalData: '',
			},
		},
	},
	resolve: {
		alias: {
			'@': resolve(__dirname, 'sources'),
		},
	},
	server: {
		port: 5173,
		strictPort: true,
		hmr: {
			host: 'localhost',
		},
	},
});
