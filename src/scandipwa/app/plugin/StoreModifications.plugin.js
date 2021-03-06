import { UPDATE_CONFIG } from 'Store/Config/Config.action';
import BrowserDatabase from 'Util/BrowserDatabase';
import GoogleTagManager, { GROUPED_PRODUCTS_GUEST } from '../component/GoogleTagManager/GoogleTagManager.component';
import GtmQuery from '../query/Gtm.query';
import ProductHelper from '../component/GoogleTagManager/utils/Product';

const handle_syncCartWithBEError = (args, callback, instance) => {
    return callback(...args)
        .then(
            (result) => {
                GoogleTagManager.getInstance().setGroupedProducts({});
                return result;
            }
        );
};

const addGtmConfigQuery = (args, callback, instance) => {
    const original = callback(...args);
    return [
        ...(Array.isArray(original) ? original : [original]),
        GtmQuery.getGTMConfiguration()
    ];
};

const addGtmToConfigReducerInitialState = (args, callback, instance) => {
    const { gtm } = BrowserDatabase.getItem('config') || { gtm: {} };

    return {
        ...callback(...args),
        gtm
    };
};

const addGtmToConfigUpdate = (args, callback, context) => {
    const [, action] = args;
    const originalUpdatedState = callback.apply(context, args);

    if (!action) {
        return originalUpdatedState;
    }

    const { config: { gtm } = {}, type } = action;

    if (type !== UPDATE_CONFIG) {
        return originalUpdatedState;
    }

    return {
        ...originalUpdatedState,
        gtm
    };
};

const afterRequestCustomerData = (args, callback, instance) => {
    const gtm = GoogleTagManager.getInstance();

    /** transfer grouped products data from guest to logged in user */
    const transferGroupedProductsData = (id) => {
        if (gtm.groupedProductsStorageName !== GROUPED_PRODUCTS_GUEST) {
            return;
        }

        const guestGroupedProducts = gtm.getGroupedProducts();
        gtm.setGroupedProducts({});
        gtm.updateGroupedProductsStorageName(id);

        const userGroupedProducts = gtm.getGroupedProducts();
        const result = ProductHelper.mergeGroupedProducts(guestGroupedProducts, userGroupedProducts);

        gtm.setGroupedProducts(result);
    };

    return callback(...args)
        .then(result => {
            transferGroupedProductsData(customer.id);
            gtm.updateGroupedProductsStorageName(customer.id);

            return result;
        });
};

export default {
    'Store/Cart/Dispatcher': {
        'member-function': {
            'handle_syncCartWithBEError': handle_syncCartWithBEError
        }
    },
    'Store/Config/Dispatcher': {
        'member-function': {
            'prepareRequest': addGtmConfigQuery
        }
    },
    'Store/Config/Reducer/getInitialState': {
        'function': addGtmToConfigReducerInitialState
    },
    'Store/Config/Reducer': {
        'function': addGtmToConfigUpdate
    },
    'Store/MyAccount/Dispatcher': {
        'member-function': {
            'requestCustomerData': afterRequestCustomerData
        }
    }
};
